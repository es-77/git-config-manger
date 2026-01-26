<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\GitService;
use Native\Desktop\Dialog;

class GitCommandManager extends Component
{
    public $directory = '';
    public $currentBranch = '';
    public $branches = [];
    public $history = [];
    public $viewMode = 'console'; // 'console' or 'history'
    public $outputLog = [];
    public $isLoading = false;

    // Branch Management
    public $newBranchName = '';
    public $renameBranchName = '';

    // Expanded Commit Details
    public $selectedCommitHash = null;
    public $commitDetails = [];

    // Stash Management
    public $stashMessage = '';
    public $stashList = [];
    public $selectedFile = null;
    public $fileDiff = '';

    public $recentRepos = [];

    public function mount(GitService $git)
    {
        $this->directory = session('current_git_dir', '');

        // Validate directory before attempting operations to prevent 500 errors
        if ($this->directory && (!is_dir($this->directory) || !$git->isGitRepo($this->directory))) {
            $this->directory = '';
            session()->forget('current_git_dir');
        }

        $this->recentRepos = $this->getStoredRepos();

        if ($this->directory) {
            $this->refreshCtx($git);
            $this->storeRepo($this->directory); // Update timestamp
        }
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
            $this->addLog("Repository not found at path: $path", 'text-red-400');
            $this->removeRepo($path);
            return;
        }

        $this->directory = $path;
        session(['current_git_dir' => $path]);
        $this->storeRepo($path);
        $this->refreshCtx($git);
        $this->addLog("Selected directory: $path");
        $this->viewMode = 'console'; // Reset to console
    }

    public function backToRepos()
    {
        $this->directory = '';
        session()->forget('current_git_dir');
        $this->recentRepos = $this->getStoredRepos();
    }

    // ... existing ...

    public function expandCommit(GitService $git, $hash)
    {
        if ($this->selectedCommitHash === $hash) {
            $this->selectedCommitHash = null;
            $this->commitDetails = [];
            return;
        }

        $this->selectedCommitHash = $hash;
        $this->commitDetails = $git->getCommitFiles($this->directory, $hash);

        // Reset diff when switching commits
        $this->selectedFile = null;
        $this->fileDiff = '';
    }

    public function showFileDiff(GitService $git, $file)
    {
        if (!$this->selectedCommitHash) return;

        $this->selectedFile = $file;
        $this->fileDiff = $git->getFileDiff($this->directory, $this->selectedCommitHash, $file);
        $this->viewMode = 'diff';
    }

    public function closeDiff()
    {
        $this->selectedFile = null;
        $this->fileDiff = '';
        $this->viewMode = 'history';
    }

    public function pickDirectory(GitService $git)
    {
        try {
            $path = Dialog::new()->folders()->open();
            if ($path) {
                // Verify it's a git repo
                if (!$git->isGitRepo($path)) {
                    $this->addLog("Selected directory is not a Git repository", 'text-yellow-400');
                    return;
                }

                $this->directory = $path;
                session(['current_git_dir' => $path]);
                $this->storeRepo($path);
                $this->refreshCtx($git);
                $this->addLog("Selected directory: $path");
            }
        } catch (\Exception $e) {
            $this->addLog("Error selecting directory: " . $e->getMessage(), 'text-red-400');
        }
    }

    public function switchBranch(GitService $git, $branch)
    {
        if (!$branch || $branch === $this->currentBranch) return;

        $this->runGitCommand($git, 'checkout', "Switching to branch '$branch'...", [$branch]);
    }

    protected function refreshCtx(GitService $git)
    {
        $this->currentBranch = $git->getCurrentBranch($this->directory);
        $this->branches = $git->getBranches($this->directory);
    }

    public function gitPull(GitService $git)
    {
        $this->runGitCommand($git, 'pull', 'Pulling changes...');
    }

    public function gitPush(GitService $git)
    {
        $this->runGitCommand($git, 'push', 'Pushing changes...');
    }

    public function gitFetch(GitService $git)
    {
        $this->runGitCommand($git, 'fetch', 'Fetching refs...');
    }

    public function showHistory(GitService $git)
    {
        if (!$this->validateRepo($git)) return;

        $this->viewMode = 'history';
        $this->history = $git->getHistory($this->directory, 50); // Fetch more items for list view
    }

    public function showConsole()
    {
        $this->viewMode = 'console';
    }

    public function gitRollback(GitService $git)
    {
        // Mixed Reset (Keep changes, undo commit)
        $this->runGitCommand($git, 'reset', 'Rolling back (Mixed Reset)...', ['mixed', 'HEAD~1']);
    }

    public function gitSoftReset(GitService $git)
    {
        // Soft Reset (Keep staged, undo commit)
        $this->runGitCommand($git, 'reset', 'Soft Resetting...', ['soft', 'HEAD~1']);
    }

    public function gitHardReset(GitService $git)
    {
        // Hard Reset (Discard all changes)
        $this->runGitCommand($git, 'reset', 'Hard Resetting...', ['hard', 'HEAD']);
    }

    public function gitStatus(GitService $git)
    {
        $this->runGitCommand($git, 'getStatus', 'Checking Status...');
        $this->showConsole();
    }

    public function gitCheckRemote(GitService $git)
    {
        $this->runGitCommand($git, 'getRemotesVerbose', 'Checking Remotes...');
        $this->showConsole();
    }

    public function gitListAllBranches(GitService $git)
    {
        $this->runGitCommand($git, 'getAllBranches', 'Listing All Branches...');
        $this->showConsole();
    }

    public function gitCreateBranch(GitService $git)
    {
        if (empty($this->newBranchName)) return;

        $safeName = $this->sanitizeBranchName($this->newBranchName);

        if ($safeName !== $this->newBranchName) {
            $this->addLog("Sanitized branch name: '{$this->newBranchName}' -> '$safeName'", 'text-yellow-300');
            $this->newBranchName = $safeName;
        }

        $this->runGitCommand($git, 'createBranch', "Creating branch '{$this->newBranchName}'...", [$this->newBranchName]);
        $this->newBranchName = ''; // Reset input
    }

    public function gitRenameBranch(GitService $git)
    {
        if (empty($this->renameBranchName)) return;

        $safeName = $this->sanitizeBranchName($this->renameBranchName);

        if ($safeName !== $this->renameBranchName) {
            $this->addLog("Sanitized branch name: '{$this->renameBranchName}' -> '$safeName'", 'text-yellow-300');
            $this->renameBranchName = $safeName;
        }

        $this->runGitCommand($git, 'renameBranch', "Renaming current to '{$this->renameBranchName}'...", [$this->renameBranchName]);
        $this->renameBranchName = ''; // Reset input
    }

    public function gitForcePush(GitService $git)
    {
        $this->runGitCommand($git, 'push', 'Force Pushing...', [true]);
    }

    // Stash Operations
    public function gitStash(GitService $git, $option = 'default')
    {
        // option: default, untracked, all
        $includeUntracked = ($option === 'untracked' || $option === 'all');
        $includeIgnored = ($option === 'all');

        $msg = $this->stashMessage ? $this->stashMessage : null;
        $desc = "Stashing changes" . ($msg ? " ($msg)" : "") . "...";

        $this->runGitCommand($git, 'stash', $desc, [$includeUntracked, $includeIgnored, $msg]);
        $this->stashMessage = ''; // Reset message
    }

    public function showStashList(GitService $git)
    {
        if (!$this->validateRepo($git)) return;

        $this->viewMode = 'stash';
        $this->stashList = $git->getStashList($this->directory);
    }

    public function gitStashApply(GitService $git, $index)
    {
        $this->runGitCommand($git, 'stashApply', "Applying stash $index...", [$index]);
        $this->showStashList($git); // Refresh list
    }

    public function gitStashPop(GitService $git, $index)
    {
        $this->runGitCommand($git, 'stashPop', "Popping stash $index...", [$index]);
        $this->showStashList($git); // Refresh list
    }

    public function gitStashDrop(GitService $git, $index)
    {
        $this->runGitCommand($git, 'stashDrop', "Dropping stash $index...", [$index]);
        $this->showStashList($git); // Refresh list
    }

    protected function runGitCommand(GitService $git, string $method, string $startMsg, array $args = [])
    {
        if (!$this->validateRepo($git)) return;

        $this->isLoading = true;
        $this->addLog("➜ $startMsg");

        // Dynamically call the method on GitService
        // Args usually only contain $path, unless it's 'reset' which takes extra args
        if ($method === 'reset') {
            $result = $git->reset($this->directory, ...$args);
        } else {
            $result = $git->$method($this->directory, ...$args);
        }

        if ($result['success']) {
            $this->addLog("✔ Success", 'text-green-400');
            if (!empty($result['output'])) {
                $this->addLog($result['output'], 'text-gray-300');
            }
            // Refresh branch status in case it changed
            $this->refreshCtx($git);
        } else {
            $this->addLog("✘ Failed", 'text-red-400');
            $this->addLog($result['error'] ?: $result['output'], 'text-red-300');
        }

        $this->addLog("----------------------------------------");
        $this->isLoading = false;
    }

    protected function validateRepo(GitService $git)
    {
        if (!$this->directory) {
            $this->addLog("⚠ No directory selected.", 'text-yellow-400');
            return false;
        }
        if (!$git->isGitRepo($this->directory)) {
            $this->addLog("⚠ Not a valid git repository: " . $this->directory, 'text-yellow-400');
            return false;
        }
        return true;
    }

    protected function addLog($message, $colorClass = 'text-gray-400')
    {
        // Add timestamp
        $time = date('H:i:s');
        $this->outputLog[] = [
            'time' => $time,
            'message' => $message,
            'color' => $colorClass
        ];
    }

    public function clearLog()
    {
        $this->outputLog = [];
    }

    protected function sanitizeBranchName($name)
    {
        // Remove quotes, common illegal characters, and trim
        $name = trim($name);
        return str_replace(['"', "'", ':', '\\', '?', '*', '[', ']', '^', '~', '{', '}'], '', $name);
    }

    public function render()
    {
        return view('livewire.git-command-manager');
    }
}
