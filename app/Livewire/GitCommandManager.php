<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\GitService;
use Native\Desktop\Dialog;

class GitCommandManager extends Component
{
    public $directory = '';
    public $currentBranch = '';
    public $outputLog = [];
    public $isLoading = false;

    public function mount(GitService $git)
    {
        $this->directory = session('current_git_dir', '');
        if ($this->directory) {
            $this->currentBranch = $git->getCurrentBranch($this->directory);
        }
    }

    public function pickDirectory(GitService $git)
    {
        $path = Dialog::new()->folders()->open();
        if ($path) {
            $this->directory = $path;
            session(['current_git_dir' => $path]);
            $this->currentBranch = $git->getCurrentBranch($this->directory);
            $this->addLog("Selected directory: $path");
        }
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
            $result = $git->$method($this->directory);
        }

        if ($result['success']) {
            $this->addLog("✔ Success", 'text-green-400');
            if (!empty($result['output'])) {
                $this->addLog($result['output'], 'text-gray-300');
            }
            // Refresh branch status in case it changed
            $this->currentBranch = $git->getCurrentBranch($this->directory);
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
