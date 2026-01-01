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

    public function pull(string $path): array
    {
        return $this->runCommand(['git', 'pull'], $path);
    }

    public function push(string $path): array
    {
        return $this->runCommand(['git', 'push'], $path);
    }

    public function fetch(string $path): array
    {
        return $this->runCommand(['git', 'fetch'], $path);
    }

    public function reset(string $path, string $mode = 'mixed', string $target = 'HEAD'): array
    {
        $cmd = ['git', 'reset'];
        if ($mode) {
            $cmd[] = "--$mode";
        }
        $cmd[] = $target;

        return $this->runCommand($cmd, $path);
    }

    public function getCurrentBranch(string $path): ?string
    {
        $result = $this->runCommand(['git', 'branch', '--show-current'], $path);
        return $result['success'] ? trim($result['output']) : null;
    }

    public function getBranches(string $path): array
    {
        // Get list of local branches
        $result = $this->runCommand(['git', 'branch', '--format=%(refname:short)'], $path);
        return $result['success'] ? array_filter(explode("\n", trim($result['output']))) : [];
    }

    public function checkout(string $path, string $branch): array
    {
        return $this->runCommand(['git', 'checkout', $branch], $path);
    }

    public function getHistory(string $path, int $limit = 15): array
    {
        $result = $this->runCommand([
            'git',
            'log',
            "-$limit",
            '--pretty=format:%h|%an|%ae|%ar|%s'
        ], $path);

        if (!$result['success']) return [];

        $lines = explode("\n", trim($result['output']));
        $history = [];

        foreach ($lines as $line) {
            $parts = explode('|', $line, 5);
            if (count($parts) === 5) {
                $history[] = [
                    'hash' => $parts[0],
                    'author_name' => $parts[1],
                    'author_email' => $parts[2],
                    'time' => $parts[3],
                    'message' => $parts[4],
                ];
            }
        }

        return $history;
    }

    public function getCommitFiles(string $path, string $hash): array
    {
        $result = $this->runCommand([
            'git',
            'show',
            '--pretty=',
            '--name-status',
            $hash
        ], $path);

        if (!$result['success']) return [];

        $lines = explode("\n", trim($result['output']));
        $files = [];

        foreach ($lines as $line) {
            $parts = preg_split('/\s+/', $line, 2);
            if (count($parts) === 2) {
                $statusChar = substr($parts[0], 0, 1);
                $status = match ($statusChar) {
                    'A' => 'added',
                    'M' => 'modified',
                    'D' => 'deleted',
                    'R' => 'renamed',
                    default => 'unknown'
                };

                $files[] = [
                    'status' => $status,
                    'file' => $parts[1]
                ];
            }
        }

        return $files;
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
