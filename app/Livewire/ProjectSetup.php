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

    protected function getSshCommand()
    {
        $cmd = 'ssh -o StrictHostKeyChecking=accept-new -v';

        try {
            $customPath = \Native\Desktop\Facades\Settings::get('ssh_config_path');
            if ($customPath && file_exists($customPath)) {
                $cmd .= ' -F ' . escapeshellarg($customPath);
            }
        } catch (\Throwable $e) {
            // Fallback to default
        }

        return $cmd;
    }

    public function runCloneCommand()
    {
        $cmd = $this->cloneCommand;
        if (!$cmd) {
            return;
        }

        // Capture the config path state for debugging
        $customPath = \Native\Desktop\Facades\Settings::get('ssh_config_path');
        $debugInfo = "Config Path: " . ($customPath ?? 'Default (Null)');

        // Execute via bash with custom SSH command
        $result = Process::env([
            'GIT_SSH_COMMAND' => $this->getSshCommand()
        ])->timeout(120)->run(['bash', '-c', $cmd]);

        if ($result->successful()) {
            $this->dispatch('notify', 'Repository cloned successfully.');

            // Apply selected profile config if set
            if ($this->cloneSelectedProfile && $this->cloneTargetDirectory) {
                // Determine the actual repo folder name
                $repoName = '';
                $url = trim($this->cloneInputUrl);

                // Extract last part of URL
                if (preg_match('/\/([^\/]+?)(\.git)?$/', $url, $matches)) {
                    $repoName = $matches[1];
                }

                if ($repoName) {
                    $repoPath = rtrim($this->cloneTargetDirectory, '/') . DIRECTORY_SEPARATOR . $repoName;
                    $this->applyProfileConfig($repoPath, $this->cloneSelectedProfile);
                } else {
                    $this->dispatch('notify', 'Could not determine repo name to set config. Please set manually.');
                }
            }
        } else {
            // Include debug info and error output
            $error = $result->errorOutput();
            $this->dispatch('notify', "Clone failed [$debugInfo]. Check logs.");

            // Log full error for user to see (maybe we can show in a modal later, but for now notify)
            // We'll append the last 500 chars which likely contains the auth error
            $shortError = substr($error, -500);
            $this->dispatch('notify', "Error details: " . $shortError);
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

        $result = Process::env([
            'GIT_SSH_COMMAND' => $this->getSshCommand()
        ])->timeout(120)->run(['bash', '-c', $cmd]);

        if ($result->successful()) {
            $this->dispatch('notify', 'Git initialized, origin set, and code pushed.');

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
