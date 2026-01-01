<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\ProfileService;
use App\Services\GitService;
use Native\Desktop\Dialog;

class ProfileManager extends Component
{
    public $profiles = [];
    public $name = '';
    public $email = '';
    public $directory = '';

    // Track current config state
    public $currentConfig = [
        'name' => 'Loading...',
        'email' => 'Loading...',
        'scope' => 'Global', // Global or Local
        'remoteUrl' => null
    ];

    public $remoteUrlInput = '';
    public $editingProfileId = null;

    public function mount(ProfileService $profileService, GitService $gitService)
    {
        $this->importGlobalProfile($profileService, $gitService);
        $this->refreshProfiles();
        $this->refreshCurrentConfig($gitService);
    }

    // ...

    public function refreshCurrentConfig(GitService $git)
    {
        $path = $this->directory && is_dir($this->directory) ? $this->directory : null;

        $scope = 'Global';
        $remoteUrl = null;

        if ($path && $git->isGitRepo($path)) {
            $scope = 'Local (Repo)';
            $remoteUrl = $git->getRemoteUrl('origin', $path);
        } elseif ($path) {
            $scope = 'Global (Not a Git Repo)';
        }

        $this->currentConfig = [
            'name' => $git->getConfig('user.name', false, $path) ?? 'Not Set',
            'email' => $git->getConfig('user.email', false, $path) ?? 'Not Set',
            'scope' => $scope,
            'remoteUrl' => $remoteUrl
        ];

        $this->remoteUrlInput = $remoteUrl;
    }

    public function updateRemoteUrl(GitService $git)
    {
        if (!$this->directory || !$git->isGitRepo($this->directory)) {
            $this->dispatch('notify', 'Invalid repository selected.');
            return;
        }

        if (empty($this->remoteUrlInput)) {
            $this->dispatch('notify', 'Remote URL cannot be empty.');
            return;
        }

        if ($git->setRemoteUrl('origin', $this->remoteUrlInput, $this->directory)) {
            $this->dispatch('notify', 'Remote origin updated successfully.');
            $this->refreshCurrentConfig($git);
        } else {
            $this->dispatch('notify', 'Failed to update remote origin.');
        }
    }

    public function importGlobalProfile(ProfileService $profileService, GitService $gitService)
    {
        $name = $gitService->getConfig('user.name', true);
        $email = $gitService->getConfig('user.email', true);

        if ($name && $email) {
            $profiles = $profileService->getProfiles();
            $exists = collect($profiles)->contains(function ($p) use ($email) {
                return strtolower($p['email']) === strtolower($email);
            });

            if (!$exists) {
                $profileService->addProfile($name, $email);
            }
        }
    }

    public function refreshProfiles()
    {
        $this->profiles = app(ProfileService::class)->getProfiles();
    }

    public function editProfile($id)
    {
        $profile = collect($this->profiles)->firstWhere('id', $id);
        if ($profile) {
            $this->name = $profile['name'];
            $this->email = $profile['email'];
            $this->editingProfileId = $id;
        }
    }

    public function cancelEdit()
    {
        $this->reset(['name', 'email', 'editingProfileId']);
    }

    public function saveProfile(ProfileService $service)
    {
        $this->validate([
            'name' => 'required|string|min:2',
            'email' => 'required|email'
        ]);

        if ($this->editingProfileId) {
            $service->updateProfile($this->editingProfileId, $this->name, $this->email);
            $this->dispatch('notify', 'Profile updated successfully');
        } else {
            $service->addProfile($this->name, $this->email);
            $this->dispatch('notify', 'Profile added successfully');
        }

        $this->reset(['name', 'email', 'editingProfileId']);
        $this->refreshProfiles();
    }

    public function deleteProfile($id, ProfileService $service)
    {
        $service->deleteProfile($id);
        $this->refreshProfiles();
        $this->dispatch('notify', 'Profile deleted');
    }

    public function applyGlobal($name, $email, GitService $git)
    {
        $git->setConfig('user.name', $name, true);
        $git->setConfig('user.email', $email, true);
        $this->refreshCurrentConfig($git);
        $this->dispatch('notify', "Global config updated to: $name <$email>");
    }



    public function pickDirectory(GitService $git)
    {
        // Bridge to native dialog
        $path = Dialog::new()->open();

        if ($path) {
            $this->directory = $path;
            $this->refreshCurrentConfig($git);
        }
    }

    public function applyLocal($name, $email, GitService $git)
    {
        if (!$this->directory || !is_dir($this->directory)) {
            $this->dispatch('notify', 'Please select a valid directory first.');
            return;
        }

        if (!$git->isGitRepo($this->directory)) {
            $this->dispatch('confirm-git-init', name: $name, email: $email);
            return;
        }

        $this->executeApplyLocal($name, $email, $git);
    }

    #[\Livewire\Attributes\On('init-repo-confirmed')]
    public function initializeAndApply($name, $email, GitService $git)
    {
        if ($git->initRepo($this->directory)) {
            $this->dispatch('notify', 'Repository initialized successfully.');
            $this->executeApplyLocal($name, $email, $git);
        } else {
            $this->dispatch('notify', 'Failed to initialize Git repository.');
        }
    }

    protected function executeApplyLocal($name, $email, GitService $git)
    {
        $git->setConfig('user.name', $name, false, $this->directory);
        $git->setConfig('user.email', $email, false, $this->directory);

        $this->refreshCurrentConfig($git);
        $this->dispatch('notify', "Local config updated for repo.");
    }

    public function render()
    {
        return view('livewire.profile-manager');
    }
}
