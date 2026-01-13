<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\File;

class LogViewer extends Component
{
    public $activeTab = 'laravel'; // 'laravel' or 'commands'
    public $laravelLogs = [];
    public $commandLogs = [];

    public function mount()
    {
        $this->refreshLogs();
    }

    public function refreshLogs()
    {
        $this->laravelLogs = $this->getLaravelLogs();
        $this->commandLogs = $this->getCommandLogs();
    }

    protected function getLaravelLogs()
    {
        $logFile = storage_path('logs/laravel.log');

        if (!File::exists($logFile)) {
            return [];
        }

        $content = File::get($logFile);
        $lines = explode("\n", $content);
        $logs = [];
        $currentLog = '';

        // Simple parsing to group multiline logs (stack traces)
        foreach ($lines as $line) {
            if (preg_match('/^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]/', $line)) {
                if (!empty($currentLog)) {
                    $logs[] = $currentLog;
                }
                $currentLog = $line;
            } else {
                $currentLog .= "\n" . $line;
            }
        }
        if (!empty($currentLog)) {
            $logs[] = $currentLog;
        }

        return array_reverse(array_slice($logs, -50)); // Return last 50 logs, reversed
    }

    protected function getCommandLogs()
    {
        $logFile = storage_path('logs/commands.json');

        if (!File::exists($logFile)) {
            return [];
        }

        $content = File::get($logFile);
        // Assuming JSON lines or a JSON array. Let's go with JSON Lines for easier appending
        $lines = explode("\n", trim($content));
        $logs = [];

        foreach ($lines as $line) {
            if (empty($line)) continue;
            $data = json_decode($line, true);
            if ($data) {
                $logs[] = $data;
            }
        }

        return array_reverse(array_slice($logs, -50));
    }

    public function clearLogs()
    {
        if ($this->activeTab === 'laravel') {
            $logFile = storage_path('logs/laravel.log');
            if (File::exists($logFile)) {
                File::put($logFile, '');
            }
        } elseif ($this->activeTab === 'commands') {
            $logFile = storage_path('logs/commands.json');
            if (File::exists($logFile)) {
                File::put($logFile, '');
            }
        }

        $this->refreshLogs();
        $this->dispatch('notify', 'Logs cleared successfully.');
    }

    public function render()
    {
        return view('livewire.log-viewer');
    }
}
