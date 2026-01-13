<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\CustomCommandService;
use App\Services\GitService;
use Native\Desktop\Dialog;
use Illuminate\Support\Facades\Storage;

class CustomCommands extends Component
{
    public $commands = [];
    public $isCreating = false;
    public $isEditing = false;

    // Form properties
    public $commandId = null;
    public $name = '';
    public $description = '';
    public $steps = ['']; // Array of command strings
    public $askDirectory = false; // New flag

    // Execution state
    public $runningCommandId = null;
    public $directory = '';
    public $executionLog = [];
    public $showOutputModal = false;

    // Directory Management
    public $recentDirectories = [];
    public $showDirectoryModal = false;
    public $pendingCommandId = null;

    public function mount(CustomCommandService $service)
    {
        $this->refreshCommands($service);
        $this->directory = session('current_git_dir', '');
        $this->loadRecentDirectories();
    }

    public function loadRecentDirectories()
    {
        if (Storage::exists('recent_directories.json')) {
            $this->recentDirectories = json_decode(Storage::get('recent_directories.json'), true) ?? [];
        }
    }

    public function addToRecents($path)
    {
        // Remove if exists to re-add at top
        $this->recentDirectories = array_values(array_diff($this->recentDirectories, [$path]));

        array_unshift($this->recentDirectories, $path);

        $this->recentDirectories = array_slice($this->recentDirectories, 0, 10); // Keep last 10
        Storage::put('recent_directories.json', json_encode($this->recentDirectories));
    }

    public function refreshCommands(CustomCommandService $service)
    {
        $this->commands = $service->getAll();
    }

    public function create()
    {
        $this->resetForm();
        $this->isCreating = true;
    }

    public function edit(CustomCommandService $service, $id)
    {
        $command = $service->get($id);
        if ($command) {
            $this->commandId = $command['id'];
            $this->name = $command['name'];
            $this->description = $command['description'] ?? '';
            $this->steps = $command['steps'] ?? [''];
            $this->askDirectory = $command['askDirectory'] ?? false;
            $this->isEditing = true;
            $this->isCreating = false;
        }
    }

    public function cancel()
    {
        $this->resetForm();
        $this->isCreating = false;
        $this->isEditing = false;
    }

    public function addStep()
    {
        $this->steps[] = '';
    }

    public function removeStep($index)
    {
        if (count($this->steps) > 1) {
            unset($this->steps[$index]);
            $this->steps = array_values($this->steps); // Re-index
        }
    }

    public function save(CustomCommandService $service)
    {
        $this->validate([
            'name' => 'required|string|min:3',
            'steps.*' => 'required|string',
        ]);

        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'steps' => array_values(array_filter($this->steps)), // Remove empty
            'askDirectory' => $this->askDirectory,
        ];

        if ($this->commandId) {
            $data['id'] = $this->commandId;
        }

        $service->save($data);

        $this->refreshCommands($service);
        $this->cancel();
    }

    public function delete(CustomCommandService $service, $id)
    {
        $service->delete($id);
        $this->refreshCommands($service);
    }

    public function pickDirectory()
    {
        $path = Dialog::new()->folders()->open();
        if ($path) {
            $this->selectDirectory($path);
        }
    }

    public function selectDirectory($path)
    {
        $this->directory = $path;
        session(['current_git_dir' => $path]);
        $this->addToRecents($path);
        $this->showDirectoryModal = false;

        if ($this->pendingCommandId) {
            $this->startExecution($this->pendingCommandId);
        }
    }

    public function run(CustomCommandService $service, GitService $git, $id)
    {
        $commandData = $service->get($id);
        if (!$commandData) return;

        if (!empty($commandData['askDirectory'])) {
            $this->pendingCommandId = $id;
            $this->showDirectoryModal = true;
            return;
        }

        if (!$this->directory) {
            // Need a repo to run commands in
            $this->dispatch('notify', ['message' => 'Please select a repository first!', 'type' => 'error']);
            return;
        }

        $this->startExecution($id);
    }

    public function startExecution($id)
    {
        // Inject services manually since this is called internally
        $service = app(CustomCommandService::class);
        $git = app(GitService::class);

        $commandData = $service->get($id);
        if (!$commandData) return;

        $this->runningCommandId = $id;
        $this->pendingCommandId = null;
        $this->executionLog = []; // Reset logs

        foreach ($commandData['steps'] as $step) {
            $parts = explode(' ', $step);
            $result = $git->runCommand($parts, $this->directory);

            $this->executionLog[] = [
                'command' => $step,
                'success' => $result['success'],
                'output'  => $result['output'],
                'error'   => $result['error'],
            ];
        }

        $this->showOutputModal = true;
        // $this->dispatch('notify', ['message' => "Executed '{$commandData['name']}' successfully."]); // Removed generic toast in favor of modal
        $this->runningCommandId = null;
    }

    public function closeOutputModal()
    {
        $this->showOutputModal = false;
        $this->executionLog = [];
    }

    protected function resetForm()
    {
        $this->commandId = null;
        $this->name = '';
        $this->description = '';
        $this->steps = [''];
        $this->askDirectory = false;
    }

    public function render()
    {
        return view('livewire.custom-commands');
    }
}
