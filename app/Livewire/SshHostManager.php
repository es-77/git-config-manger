<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\SshConfigService;
use Native\Desktop\Dialog;

class SshHostManager extends Component
{
    public $hosts = [];

    // Form fields
    public $alias = '';
    public $hostName = '';
    public $user = '';
    public $identityFile = '';
    public $port = '';

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
        if ($this->identityFile) $details['IdentityFile'] = $this->identityFile;
        if ($this->port) $details['Port'] = $this->port;

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
        $this->reset(['alias', 'hostName', 'user', 'identityFile', 'port', 'isEditing', 'originalAlias']);
    }

    public function render()
    {
        return view('livewire.ssh-host-manager');
    }
}
