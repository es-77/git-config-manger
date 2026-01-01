<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\ProfileService;
use App\Services\GitService;
use Native\Desktop\Facades\Window;

class Spotlight extends Component
{
    public $search = '';
    public $profiles = [];
    public $activeIndex = 0;

    public function mount(ProfileService $service)
    {
        $this->profiles = $service->getProfiles();
    }

    public function updatedSearch()
    {
        $this->activeIndex = 0;
    }

    public function getFilteredProfilesProperty()
    {
        if (empty($this->search)) {
            return $this->profiles;
        }

        return collect($this->profiles)->filter(function ($profile) {
            return str_contains(strtolower($profile['name']), strtolower($this->search)) ||
                str_contains(strtolower($profile['email']), strtolower($this->search));
        })->values()->all();
    }

    public function selectProfile($index, GitService $git)
    {
        $profiles = $this->getFilteredProfilesProperty();

        if (isset($profiles[$index])) {
            $profile = $profiles[$index];

            // For spotlight, we assume applying to CURRENT working directory or prompt?
            // Since we can't easily know the user's "current terminal directory" from the global shortcut context
            // checking "current directory" might be ambiguous.
            // Requirement says: "apply it as local config to the currently selected directory (or prompt for directory if none selected)."
            // Since this is a global popup, we probably need to PROMPT or have a way to know context.
            // Let's prompt for directory for now as it's safer.

            $path = \Native\Desktop\Dialog::new()->open();

            if ($path && $git->isGitRepo($path)) {
                $git->setConfig('user.name', $profile['name'], false, $path);
                $git->setConfig('user.email', $profile['email'], false, $path);

                // Close window after success
                Window::close('spotlight');
            }
        }
    }

    public function handleEnter(GitService $git)
    {
        $this->selectProfile($this->activeIndex, $git);
    }

    public function moveSelection($direction)
    {
        $count = count($this->getFilteredProfilesProperty());
        if ($count === 0) return;

        if ($direction === 'up') {
            $this->activeIndex = ($this->activeIndex - 1 + $count) % $count;
        } else {
            $this->activeIndex = ($this->activeIndex + 1) % $count;
        }
    }

    public function render()
    {
        return view('livewire.spotlight', [
            'filteredProfiles' => $this->getFilteredProfilesProperty()
        ])->layout('components.layouts.app');
        // Note: we might want a different, minimal layout for spotlight
    }
}
