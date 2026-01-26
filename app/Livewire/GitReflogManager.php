<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\GitService;
use Native\Desktop\Dialog;

class GitReflogManager extends Component
{
    public $repositoryPath = '';
    public $reflog = [];
    public $currentHead = null;
    public $limit = 50;
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

        // Remove if exists (to move to top)
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
        $this->storeRepo($path); // Update timestamp
        $this->loadReflog($git);
    }

    public function backToRepos()
    {
        $this->repositoryPath = '';
        $this->reflog = [];
        $this->currentHead = null;
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

            // Verify it's a git repository (or inside one)
            // Use resolved path from git rev-parse if possible, but for now just basic check
            if (!$git->isGitRepo($path)) {
                $this->dispatch('notify', 'Selected directory is not a Git repository');
                return;
            }

            $this->repositoryPath = $path;
            $this->storeRepo($path);
            $this->loadReflog($git);
            $this->dispatch('notify', 'Repository loaded successfully');
        } catch (\Exception $e) {
            $this->dispatch('notify', 'Error: ' . $e->getMessage());
        }
    }

    public function loadReflog(GitService $git)
    {
        if (!$this->repositoryPath) {
            return;
        }

        try {
            $this->reflog = $git->getReflog($this->repositoryPath, $this->limit);
            $this->currentHead = $git->getHeadCommit($this->repositoryPath);
        } catch (\Exception $e) {
            $this->dispatch('notify', 'Error loading reflog: ' . $e->getMessage());
        }
    }

    public function checkout(GitService $git, $ref)
    {
        if (!$this->repositoryPath) return;

        try {
            $result = $git->checkoutRef($this->repositoryPath, $ref);
            if ($result['success']) {
                $this->dispatch('notify', "Checked out $ref successfully");
                $this->loadReflog($git); // Reload to show new HEAD
            } else {
                $this->dispatch('notify', 'Checkout failed: ' . $result['error']);
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', 'Error: ' . $e->getMessage());
        }
    }

    public function resetRef(GitService $git, $ref, $mode = 'mixed')
    {
        if (!$this->repositoryPath) return;

        try {
            // Confirm action
            // Note: NativePHP doesn't have a simple synchronous confirm dialog that blocks PHP execution easily in Livewire context without some work, 
            // but we'll proceed for now. In a real app, maybe use a modal.

            $result = $git->reset($this->repositoryPath, $mode, $ref);
            if ($result['success']) {
                $this->dispatch('notify', "Reset ($mode) to $ref successfully");
                $this->loadReflog($git);
            } else {
                $this->dispatch('notify', 'Reset failed: ' . $result['error']);
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', 'Error: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.git-reflog-manager')->layout('components.layouts.app');
    }
}
