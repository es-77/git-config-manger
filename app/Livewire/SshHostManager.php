<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\SshConfigService;
use Native\Desktop\Dialog;
use Illuminate\Support\Facades\Process;

class SshHostManager extends Component
{
    public $hosts = [];

    // Form fields
    public $alias = '';
    public $hostName = '';
    public $user = '';
    public $identityFile = '';
    public $port = '';
    public $preferredAuthentications = '';

    // Clone Helper
    public $cloneInputUrl = '';
    public $cloneSelectedHost = '';
    public $cloneTargetDirectory = '';

    public $isEditing = false;
    public $originalAlias = '';

    public function mount(SshConfigService $service)
    {
        $this->refreshHosts($service);
    }

    public function refreshHosts(SshConfigService $service)
    {
        $this->hosts = $service->getHosts();
    }

    public function pickIdentityFile()
    {
        $path = Dialog::new()->open();
        if ($path) {
            $this->identityFile = $path;
        }
    }

    public function saveHost(SshConfigService $service)
    {
        $this->validate([
            'alias' => 'required|string|min:1',
            'hostName' => 'required|string',
        ]);

        $details = [
            'HostName' => $this->hostName,
        ];
        if ($this->user) $details['User'] = $this->user;
        if ($this->identityFile) $details['IdentityFile'] = $this->contractPath($this->identityFile);
        if ($this->port) $details['Port'] = $this->port;
        if ($this->preferredAuthentications) $details['PreferredAuthentications'] = $this->preferredAuthentications;

        $newHostData = [
            'Host' => $this->alias,
            'details' => $details
        ];

        // If editing and alias changed, delete old one first
        if ($this->isEditing && $this->originalAlias !== $this->alias) {
            $service->deleteHost($this->originalAlias);
        }

        if ($service->saveHost($newHostData)) {
            $this->dispatch('notify', 'SSH Host saved successfully.');
            $this->resetForm();
            $this->refreshHosts($service);
        } else {
            $this->dispatch('notify', 'Error saving SSH Host.');
        }
    }

    public function editHost($alias)
    {
        $host = collect($this->hosts)->firstWhere('Host', $alias);
        if ($host) {
            $this->alias = $alias;
            $this->originalAlias = $alias;
            $this->hostName = $host['details']['HostName'] ?? '';
            $this->user = $host['details']['User'] ?? '';
            $this->identityFile = $host['details']['IdentityFile'] ?? '';
            $this->port = $host['details']['Port'] ?? '';
            $this->preferredAuthentications = $host['details']['PreferredAuthentications'] ?? '';
            $this->isEditing = true;
        }
    }

    public function deleteHost($alias, SshConfigService $service)
    {
        if ($service->deleteHost($alias)) {
            $this->dispatch('notify', 'SSH Host deleted.');
            $this->refreshHosts($service);
        }
    }

    public function cancelEdit()
    {
        $this->resetForm();
    }

    protected function resetForm()
    {
        $this->reset(['alias', 'hostName', 'user', 'identityFile', 'port', 'preferredAuthentications', 'isEditing', 'originalAlias']);
    }

    public function copyPublicKey()
    {
        if (empty($this->identityFile)) {
            $this->dispatch('notify', 'No identity file selected.');
            return;
        }

        $expandedPath = $this->expandPath($this->identityFile);
        $pubKeyPath = $expandedPath . '.pub';

        if (file_exists($pubKeyPath)) {
            $content = trim(file_get_contents($pubKeyPath));
            $this->dispatch('copy-to-clipboard', content: $content);
            $this->dispatch('notify', 'Public key copied to clipboard.');
        } else {
            $this->dispatch('notify', 'Public key file (.pub) not found.');
        }
    }

    protected function expandPath($path)
    {
        if (str_starts_with($path, '~/')) {
            $home = getenv('HOME') ?: $_SERVER['HOME'] ?? '';
            if (empty($home) && function_exists('posix_getpwuid')) {
                $home = posix_getpwuid(posix_getuid())['dir'];
            }
            return $home . substr($path, 1);
        }
        return $path;
    }

    protected function contractPath($path)
    {
        $home = getenv('HOME') ?: $_SERVER['HOME'] ?? '';
        if (empty($home) && function_exists('posix_getpwuid')) {
            $home = posix_getpwuid(posix_getuid())['dir'];
        }

        if (str_starts_with($path, $home)) {
            return '~' . substr($path, strlen($home));
        }
        return $path;
    }

    public function generateNewKey($filename)
    {
        if (empty($filename)) {
            $this->dispatch('notify', 'Filename is required.');
            return;
        }

        $sshDir = $this->expandPath('~/.ssh');
        if (!file_exists($sshDir)) {
            mkdir($sshDir, 0700, true);
        }

        $path = $sshDir . '/' . $filename;

        if (file_exists($path)) {
            $this->dispatch('notify', 'Key already exists with this name.');
            return;
        }

        // Generate ED25519 key
        $result = Process::run(['ssh-keygen', '-t', 'ed25519', '-f', $path, '-N', '', '-q']);

        if ($result->successful()) {
            $this->identityFile = $path;
            $this->dispatch('notify', 'SSH Key generated successfully.');
            $this->dispatch('close-modal', 'key-gen-modal');
        } else {
            $this->dispatch('notify', 'Failed to generate SSH Key: ' . $result->errorOutput());
        }
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
        } else {
            $this->dispatch('notify', 'Clone failed: ' . $result->errorOutput());
        }
    }

    public function render()
    {
        return view('livewire.ssh-host-manager');
    }
}
