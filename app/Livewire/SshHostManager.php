<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\SshConfigService;
use Native\Desktop\Dialog;
use Native\Desktop\Facades\Settings;
use Illuminate\Support\Facades\Process;

class SshHostManager extends Component
{
    public $hosts = [];

    // Form fields
    public $alias = '';
    public $hostName = 'github.com';
    public $user = '';
    public $identityFile = '';
    public $port = '';
    public $preferredAuthentications = 'publickey';

    public $newKeyLocation = '';
    public $customConfigPath = '';

    public $isEditing = false;
    public $originalAlias = '';

    public function mount(SshConfigService $service)
    {
        $service->normalizeConfig();
        $this->refreshHosts($service);
        $this->newKeyLocation = $service->expandPath('~/.ssh');
        $this->customConfigPath = Settings::get('ssh_config_path') ?? '';
    }

    public function refreshHosts(SshConfigService $service)
    {
        $this->hosts = $service->getHosts();
    }

    public function pickIdentityFile()
    {
        $path = Dialog::new()
            ->title('Select Identity File')
            ->properties(['openFile', 'showHiddenFiles'])
            ->open();

        if ($path) {
            $this->identityFile = $path;
        }
    }

    public function pickNewKeyLocation()
    {
        $path = Dialog::new()
            ->title('Select Folder for New Key')
            ->properties(['openDirectory', 'showHiddenFiles', 'createDirectory'])
            ->open();

        if ($path) {
            $this->newKeyLocation = $path;
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
        if ($this->identityFile) $details['IdentityFile'] = $service->contractPath($this->identityFile);
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

    public function copyPublicKey(SshConfigService $service)
    {
        if (empty($this->identityFile)) {
            $this->dispatch('notify', 'No identity file selected.');
            return;
        }

        $expandedPath = $service->expandPath($this->identityFile);
        $pubKeyPath = $expandedPath . '.pub';

        if (file_exists($pubKeyPath)) {
            $content = trim(file_get_contents($pubKeyPath));
            $this->dispatch('copy-to-clipboard', content: $content);
            $this->dispatch('notify', 'Public key copied to clipboard.');
        } else {
            $this->dispatch('notify', 'Public key file (.pub) not found.');
        }
    }


    public function generateNewKey($filename)
    {
        if (empty($filename)) {
            $this->dispatch('notify', 'Filename is required.');
            return;
        }

        // Use selected location or default to ~/.ssh
        $service = app(SshConfigService::class);
        $targetDir = $this->newKeyLocation ? $service->expandPath($this->newKeyLocation) : $service->expandPath('~/.ssh');

        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0700, true);
        }

        $path = $targetDir . DIRECTORY_SEPARATOR . $filename;

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

    public function render()
    {
        return view('livewire.ssh-host-manager');
    }

    public function pickSshConfigFolder()
    {
        $path = Dialog::new()
            ->title('Select SSH Configuration Folder')
            ->properties(['openDirectory', 'showHiddenFiles', 'createDirectory'])
            ->open();

        if ($path) {
            $configPath = $path . DIRECTORY_SEPARATOR . 'config';
            Settings::set('ssh_config_path', $configPath);
            $this->customConfigPath = $configPath;

            $this->dispatch('notify', 'SSH Config path updated.');
            $this->refreshHosts(app(SshConfigService::class));
        }
    }

    public function updateConfigPath()
    {
        if (empty($this->customConfigPath)) {
            $this->resetSshConfigPath();
            return;
        }

        Settings::set('ssh_config_path', $this->customConfigPath);
        $this->dispatch('notify', 'SSH Config path updated.');
        $this->refreshHosts(app(SshConfigService::class));
    }

    public function resetSshConfigPath()
    {
        Settings::set('ssh_config_path', null);
        $this->customConfigPath = '';
        $this->dispatch('notify', 'SSH Config path reset to default.');
        $this->refreshHosts(app(SshConfigService::class));
    }
}
