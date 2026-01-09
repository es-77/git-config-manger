<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CustomCommandService
{
    protected $file = 'custom_commands.json';

    public function getAll(): array
    {
        if (!Storage::exists($this->file)) {
            return [];
        }

        return json_decode(Storage::get($this->file), true) ?? [];
    }

    public function get(string $id): ?array
    {
        $commands = $this->getAll();
        foreach ($commands as $cmd) {
            if ($cmd['id'] === $id) {
                return $cmd;
            }
        }
        return null;
    }

    public function save(array $data): array
    {
        $commands = $this->getAll();

        if (isset($data['id'])) {
            // Update existing
            foreach ($commands as &$cmd) {
                if ($cmd['id'] === $data['id']) {
                    $cmd = array_merge($cmd, $data);
                    $cmd['updatedAt'] = now()->toIso8601String();
                    // Save and return this specific updated command
                    Storage::put($this->file, json_encode($commands, JSON_PRETTY_PRINT));
                    return $cmd;
                }
            }
        } else {
            // Create new
            $data['id'] = (string) Str::uuid();
            $data['createdAt'] = now()->toIso8601String();
            $data['updatedAt'] = now()->toIso8601String();
            $commands[] = $data;
        }

        Storage::put($this->file, json_encode($commands, JSON_PRETTY_PRINT));

        return $data;
    }

    public function delete(string $id): bool
    {
        $commands = $this->getAll();
        $initialCount = count($commands);

        $commands = array_filter($commands, fn($cmd) => $cmd['id'] !== $id);

        if (count($commands) !== $initialCount) {
            Storage::put($this->file, json_encode(array_values($commands), JSON_PRETTY_PRINT));
            return true;
        }

        return false;
    }
}
