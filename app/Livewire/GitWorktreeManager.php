<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\GitService;
use Native\Desktop\Dialog;

class GitWorktreeManager extends Component
{
    public $repositoryPath = '';
    public $worktrees = [];
    public $newBranch = '';
    public $newPath = '';
    public $createNewBranch = false;
    public $loading = false;
    public $recentRepos = [];

    public function mount()
    {
        $this->recentRepos = $this->getStoredRepos();
    }

    protected function getStoredRepos(): array
    {
        $path = storage_path('app/recent-repos.json');
        if (file_exists($path)) {
            return json_decode(file_get_contents($path), true) ?? [];
        }
        return [];
    }

    protected function storeRepo(string $path)
    {
        $repos = $this->getStoredRepos();
        $name = basename($path);

        // Remove if exists
        $repos = array_filter($repos, fn($r) => $r['path'] !== $path);

        // Add to top
        array_unshift($repos, [
            'path' => $path,
            'name' => $name,
            'last_accessed' => now()->toDateTimeString()
        ]);

        // Keep only last 10
        $repos = array_slice($repos, 0, 10);

        // Ensure directory exists
        if (!is_dir(dirname(storage_path('app/recent-repos.json')))) {
            mkdir(dirname(storage_path('app/recent-repos.json')), 0755, true);
        }

        file_put_contents(storage_path('app/recent-repos.json'), json_encode($repos));
        $this->recentRepos = $repos;
    }

    public function removeRepo($path)
    {
        $repos = $this->getStoredRepos();
        $repos = array_filter($repos, fn($r) => $r['path'] !== $path);
        file_put_contents(storage_path('app/recent-repos.json'), json_encode($repos));
        $this->recentRepos = $repos;
    }

    public function openRecent($path, GitService $git)
    {
        if (!is_dir($path)) {
            $this->dispatch('notify', 'Repository not found at path: ' . $path);
            $this->removeRepo($path);
            return;
        }

        $this->repositoryPath = $path;
        $this->storeRepo($path);
        $this->loadWorktrees($git);
    }

    public function backToRepos()
    {
        $this->repositoryPath = '';
        $this->worktrees = [];
        $this->recentRepos = $this->getStoredRepos();
    }

    public function selectRepository(GitService $git)
    {
        try {
            $path = Dialog::new()
                ->title('Select Git Repository')
                ->folders()
                ->open();

            if (!$path) {
                return;
            }

            // Verify it's a git repository
            if (!$git->isGitRepo($path)) {
                $this->dispatch('notify', 'Selected directory is not a Git repository');
                return;
            }

            $this->repositoryPath = $path;
            $this->storeRepo($path);
            $this->loadWorktrees($git);
            $this->dispatch('notify', 'Repository loaded successfully');
        } catch (\Exception $e) {
            $this->dispatch('notify', 'Error: ' . $e->getMessage());
        }
    }

    public function loadWorktrees(GitService $git)
    {
        if (!$this->repositoryPath) {
            return;
        }

        try {
            $this->loading = true;
            $worktreeList = $git->listWorktrees($this->repositoryPath);

            // Enhance each worktree with status information
            foreach ($worktreeList as &$worktree) {
                $status = $git->getWorktreeStatus($worktree['path']);
                $worktree['clean'] = $status['clean'] ?? false;
                $worktree['isMain'] = $worktree['path'] === $this->repositoryPath;
            }

            $this->worktrees = $worktreeList;
        } catch (\Exception $e) {
            $this->dispatch('notify', 'Error loading worktrees: ' . $e->getMessage());
        } finally {
            $this->loading = false;
        }
    }

    public function createWorktree(GitService $git)
    {
        $this->validate([
            'newBranch' => 'required|string|min:1',
            'newPath' => 'required|string|min:1',
        ]);

        if (!$this->repositoryPath) {
            $this->dispatch('notify', 'Please select a repository first');
            return;
        }

        try {
            $this->loading = true;

            // Check if branch already exists in a worktree (unless creating new)
            if (!$this->createNewBranch) {
                foreach ($this->worktrees as $wt) {
                    if (isset($wt['branch']) && $wt['branch'] === $this->newBranch) {
                        $this->dispatch('notify', 'Branch "' . $this->newBranch . '" is already checked out in another worktree');
                        return;
                    }
                }
            }

            $result = $git->addWorktree(
                $this->repositoryPath,
                $this->newPath,
                $this->newBranch,
                $this->createNewBranch
            );

            if ($result['success']) {
                $this->dispatch('notify', 'Worktree created successfully');
                $this->newBranch = '';
                $this->newPath = '';
                $this->createNewBranch = false;
                $this->loadWorktrees($git);
            } else {
                $this->dispatch('notify', 'Failed to create worktree: ' . ($result['error'] ?: $result['output']));
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', 'Error: ' . $e->getMessage());
        } finally {
            $this->loading = false;
        }
    }

    public function removeWorktree(GitService $git, string $path)
    {
        if (!$this->repositoryPath) {
            return;
        }

        // Prevent removing main worktree
        if ($path === $this->repositoryPath) {
            $this->dispatch('notify', 'Cannot remove the main working tree');
            return;
        }

        try {
            $this->loading = true;
            $result = $git->removeWorktree($this->repositoryPath, $path, false);

            if ($result['success']) {
                $this->dispatch('notify', 'Worktree removed successfully');
                $this->loadWorktrees($git);
            } else {
                // If it fails, might have uncommitted changes - show error
                $this->dispatch('notify', 'Failed to remove worktree: ' . ($result['error'] ?: 'May have uncommitted changes. Use force remove if needed.'));
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', 'Error: ' . $e->getMessage());
        } finally {
            $this->loading = false;
        }
    }

    public function forceRemoveWorktree(GitService $git, string $path)
    {
        if (!$this->repositoryPath || $path === $this->repositoryPath) {
            return;
        }

        try {
            $this->loading = true;
            $result = $git->removeWorktree($this->repositoryPath, $path, true);

            if ($result['success']) {
                $this->dispatch('notify', 'Worktree force removed successfully');
                $this->loadWorktrees($git);
            } else {
                $this->dispatch('notify', 'Failed to remove worktree: ' . ($result['error'] ?: $result['output']));
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', 'Error: ' . $e->getMessage());
        } finally {
            $this->loading = false;
        }
    }

    public function openWorktree(string $path)
    {
        try {
            // Use NativePHP to open the folder in file explorer
            if (PHP_OS_FAMILY === 'Windows') {
                exec('explorer "' . str_replace('/', '\\', $path) . '"');
            } elseif (PHP_OS_FAMILY === 'Darwin') {
                exec('open "' . $path . '"');
            } else {
                exec('xdg-open "' . $path . '"');
            }
            $this->dispatch('notify', 'Opening folder...');
        } catch (\Exception $e) {
            $this->dispatch('notify', 'Error opening folder: ' . $e->getMessage());
        }
    }

    public function pickPath()
    {
        try {
            $path = Dialog::new()
                ->title('Select Worktree Location')
                ->folders()
                ->open();

            if ($path) {
                $this->newPath = $path;
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', 'Error: ' . $e->getMessage());
        }
    }

    public function updatedNewBranch()
    {
        // Auto-suggest path based on branch name
        if ($this->repositoryPath && $this->newBranch) {
            $parentDir = dirname($this->repositoryPath);
            $repoName = basename($this->repositoryPath);
            $suggestedPath = $parentDir . DIRECTORY_SEPARATOR . $repoName . '-' . $this->newBranch;

            // Only update if user hasn't manually set a path
            if (empty($this->newPath)) {
                $this->newPath = $suggestedPath;
            }
        }
    }

    public function render()
    {
        return view('livewire.git-worktree-manager')->layout('components.layouts.app');
    }
}
