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

    // File Diff Details
    public $selectedFile = null;
    public $fileDiff = '';

    public function mount(GitService $git)
    {
        $this->directory = session('current_git_dir', '');
        if ($this->directory) {
            $this->refreshCtx($git);
        }
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
        $path = Dialog::new()->folders()->open();
        if ($path) {
            $this->directory = $path;
            session(['current_git_dir' => $path]);
            $this->refreshCtx($git);
            $this->addLog("Selected directory: $path");
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
        $this->runGitCommand($git, 'createBranch', "Creating branch '{$this->newBranchName}'...", [$this->newBranchName]);
        $this->newBranchName = ''; // Reset input
    }

    public function gitRenameBranch(GitService $git)
    {
        if (empty($this->renameBranchName)) return;
        $this->runGitCommand($git, 'renameBranch', "Renaming current to '{$this->renameBranchName}'...", [$this->renameBranchName]);
        $this->renameBranchName = ''; // Reset input
    }

    public function gitForcePush(GitService $git)
    {
        $this->runGitCommand($git, 'push', 'Force Pushing...', [true]);
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

    public function render()
    {
        return view('livewire.git-command-manager');
    }
}
