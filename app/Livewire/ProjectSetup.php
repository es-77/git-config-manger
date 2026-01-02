<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\SshConfigService;
use App\Services\ProfileService;
use Native\Desktop\Dialog;
use Illuminate\Support\Facades\Process;

class ProjectSetup extends Component
{
    public $hosts = [];

    // Clone Helper
    public $cloneInputUrl = '';
    public $cloneSelectedHost = '';
    public $cloneTargetDirectory = '';

    public $cloneSelectedProfile = '';

    // Origin Helper
    public $originInputUrl = '';
    public $originSelectedHost = '';
    public $originProjectDirectory = '';
    public $originSelectedProfile = '';

    public $profiles = [];

    public function mount(SshConfigService $service, ProfileService $profileService)
    {
        $this->hosts = $service->getHosts();
        $this->profiles = $profileService->getProfiles();
    }

    public function getCloneCommandProperty()
    {
        if (empty($this->cloneInputUrl) || empty($this->cloneSelectedHost)) {
            return '';
        }

        $url = trim($this->cloneInputUrl);
        $path = '';

        // Matches git@host:path or https://host/path
        if (preg_match('/^(?:git@|https:\/\/)(?:[\w\.-]+)(?::|\/)(.+)$/', $url, $matches)) {
            $path = $matches[1];
        } else {
            // Fallback: assume whole string is path if no protocol
            $path = $url;
        }

        $cmd = "git clone git@" . $this->cloneSelectedHost . ":" . $path;

        if ($this->cloneTargetDirectory) {
            return "cd " . escapeshellarg($this->cloneTargetDirectory) . " && " . $cmd;
        }

        return $cmd;
    }

    public function pickCloneTargetDirectory()
    {
        $path = Dialog::new()->folders()->open();
        if ($path) {
            $this->cloneTargetDirectory = $path;
        }
    }

    public function copyCloneCommand()
    {
        $cmd = $this->cloneCommand;
        if ($cmd) {
            $this->dispatch('copy-to-clipboard', content: $cmd);
            $this->dispatch('notify', 'Clone command copied to clipboard.');
        }
    }

    public function runCloneCommand()
    {
        $cmd = $this->cloneCommand;
        if (!$cmd) {
            return;
        }

        // Execute via bash. We set GIT_SSH_COMMAND to accept new host keys automatically
        // to prevent the process from hanging on the "Are you sure..." prompt.
        $result = Process::env([
            'GIT_SSH_COMMAND' => 'ssh -o StrictHostKeyChecking=accept-new'
        ])->run(['bash', '-c', $cmd]);

        if ($result->successful()) {
            $this->dispatch('notify', 'Repository cloned successfully.');

            // Apply selected profile config if set
            if ($this->cloneSelectedProfile && $this->cloneTargetDirectory) {
                $this->applyProfileConfig($this->cloneTargetDirectory, $this->cloneSelectedProfile);
            }
        } else {
            $this->dispatch('notify', 'Clone failed: ' . $result->errorOutput());
        }
    }

    public function getOriginCommandProperty()
    {
        if (empty($this->originInputUrl) || empty($this->originSelectedHost)) {
            return '';
        }

        $url = trim($this->originInputUrl);
        $path = '';

        // Matches git@host:path or https://host/path
        if (preg_match('/^(?:git@|https:\/\/)(?:[\w\.-]+)(?::|\/)(.+)$/', $url, $matches)) {
            $path = $matches[1];
        } else {
            // Fallback: assume whole string is path if no protocol
            $path = $url;
        }

        // git remote add origin git@alias:user/repo.git
        $newUrl = "git@" . $this->originSelectedHost . ":" . $path;

        $cmd = "git init && (git remote add origin " . $newUrl . " || git remote set-url origin " . $newUrl . ") && git branch -M main && git push -u origin main";

        if ($this->originProjectDirectory) {
            return "cd " . escapeshellarg($this->originProjectDirectory) . " && " . $cmd;
        }

        return $cmd;
    }

    public function pickOriginProjectDirectory()
    {
        $path = Dialog::new()->folders()->open();
        if ($path) {
            $this->originProjectDirectory = $path;
        }
    }

    public function copyOriginCommand()
    {
        $cmd = $this->originCommand;
        if ($cmd) {
            $this->dispatch('copy-to-clipboard', content: $cmd);
            $this->dispatch('notify', 'Origin command copied to clipboard.');
        }
    }

    public function runOriginCommand()
    {
        if (empty($this->originProjectDirectory)) {
            $this->dispatch('notify', 'Please select a project directory first.');
            return;
        }

        $cmd = $this->originCommand;
        if (!$cmd) {
            return;
        }

        // Use strict host key checking accept-new for the push as well
        $result = Process::env([
            'GIT_SSH_COMMAND' => 'ssh -o StrictHostKeyChecking=accept-new'
        ])->timeout(120)->run(['bash', '-c', $cmd]);

        if ($result->successful()) {
            $this->dispatch('notify', 'Git initialized, origin set, and code pushed.');

            // Apply selected profile config if set
            if ($this->originSelectedProfile && $this->originProjectDirectory) {
                $this->applyProfileConfig($this->originProjectDirectory, $this->originSelectedProfile);
            }
        } else {
            $this->dispatch('notify', 'Operation failed: ' . $result->errorOutput());
        }
    }

    protected function applyProfileConfig($directory, $profileId)
    {
        $profile = collect($this->profiles)->firstWhere('id', $profileId);
        if (!$profile) return;

        $commands = [
            "git config user.name " . escapeshellarg($profile['name']),
            "git config user.email " . escapeshellarg($profile['email'])
        ];

        foreach ($commands as $cmd) {
            Process::run(['bash', '-c', "cd " . escapeshellarg($directory) . " && " . $cmd]);
        }

        $this->dispatch('notify', 'Local user config set for: ' . $profile['name']);
    }

    public function render()
    {
        return view('livewire.project-setup');
    }
}
