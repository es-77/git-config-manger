<?php

namespace App\Services;

use Illuminate\Support\Facades\Process;

class GitService
{
    public function setConfig(string $key, string $value, bool $global = false, ?string $path = null): array
    {
        $command = ['git', 'config'];
        if ($global) {
            $command[] = '--global';
        } elseif ($path) {
            $command = array_merge($command, ['--local']);
        }

        $command = array_merge($command, [$key, $value]);

        return $this->runCommand($command, $path);
    }

    public function getConfig(string $key, bool $global = false, ?string $path = null): ?string
    {
        $command = ['git', 'config'];
        if ($global) {
            $command[] = '--global';
        } elseif ($path) {
            $command = array_merge($command, ['--local']);
        }

        $command[] = $key;

        $result = $this->runCommand($command, $path);

        return $result['success'] ? trim($result['output']) : null;
    }

    public function getRemotes(?string $path): array
    {
        if (!$path) return [];

        $result = $this->runCommand(['git', 'remote', '-v'], $path);

        return $result['success'] ? explode("\n", trim($result['output'])) : [];
    }

    public function isGitRepo(string $path): bool
    {
        $result = $this->runCommand(['git', 'rev-parse', '--is-inside-work-tree'], $path);
        return $result['success'] && trim($result['output']) === 'true';
    }

    public function initRepo(string $path): bool
    {
        $result = $this->runCommand(['git', 'init'], $path);
        return $result['success'];
    }

    public function getRemoteUrl(string $remote, string $path): ?string
    {
        $result = $this->runCommand(['git', 'remote', 'get-url', $remote], $path);
        return $result['success'] ? trim($result['output']) : null;
    }

    public function setRemoteUrl(string $remote, string $url, string $path): bool
    {
        // Check if remote exists, if not add it, if yes set-url
        $remotes = $this->getRemotes($path);
        $exists = false;
        foreach ($remotes as $line) {
            if (str_starts_with($line, $remote . "\t")) {
                $exists = true;
                break;
            }
        }

        if ($exists) {
            $result = $this->runCommand(['git', 'remote', 'set-url', $remote, $url], $path);
        } else {
            $result = $this->runCommand(['git', 'remote', 'add', $remote, $url], $path);
        }

        return $result['success'];
    }

    protected function runCommand(array $command, ?string $path = null): array
    {
        // Safe execution using Laravel Process
        $process = $path ? Process::path($path)->run($command) : Process::run($command);

        return [
            'success' => $process->successful(),
            'output' => $process->output(),
            'error' => $process->errorOutput(),
        ];
    }
}
