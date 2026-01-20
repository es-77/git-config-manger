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

    public function push(string $path, bool $force = false): array
    {
        $cmd = ['git', 'push'];
        if ($force) {
            $cmd[] = '--force';
        }
        return $this->runCommand($cmd, $path);
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

    public function createBranch(string $path, string $name): array
    {
        return $this->runCommand(['git', 'checkout', '-b', $name], $path);
    }

    public function renameBranch(string $path, string $newName): array
    {
        return $this->runCommand(['git', 'branch', '-m', $newName], $path);
    }

    public function getStatus(string $path): array
    {
        // -s for short status, but normal status is more verbose and readable for our console
        return $this->runCommand(['git', 'status'], $path);
    }

    public function getRemotesVerbose(string $path): array
    {
        return $this->runCommand(['git', 'remote', '-v'], $path);
    }

    public function getAllBranches(string $path): array
    {
        return $this->runCommand(['git', 'branch', '-a'], $path);
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

    public function getFileDiff(string $path, string $hash, string $file): string
    {
        $result = $this->runCommand([
            'git',
            'show',
            '--pretty=',
            '--patch',
            $hash,
            '--',
            $file
        ], $path);

        return $result['success'] ? $result['output'] : '';
    }

    public function runCommand(array $command, ?string $path = null): array
    {
        // Safe execution using Laravel Process
        $process = $path ? Process::path($path)->run($command) : Process::run($command);

        $result = [
            'success' => $process->successful(),
            'output' => $process->output(),
            'error' => $process->errorOutput(),
        ];

        $this->logCommandResult(implode(' ', $command), $path, $result);

        return $result;
    }

    public function logCommandResult(string $commandStr, ?string $path, array $result): void
    {
        // Log the command
        try {
            $logEntry = [
                'command' => $commandStr,
                'path' => $path,
                'timestamp' => now()->toDateTimeString(),
                'success' => $result['success'],
                'output' => $this->truncateOutput($result['output']),
                'error' => $result['error'],
            ];

            $logFile = storage_path('logs/commands.json');
            // Append as JSON line
            file_put_contents($logFile, json_encode($logEntry) . PHP_EOL, FILE_APPEND);
        } catch (\Exception $e) {
            // Silently fail logging to not disrupt operation
        }
    }

    protected function truncateOutput(string $output, int $length = 500): string
    {
        if (strlen($output) > $length) {
            return substr($output, 0, $length) . '... [truncated]';
        }
        return $output;
    }

    public function stash(string $path, bool $includeUntracked = false, bool $includeIgnored = false, ?string $message = null): array
    {
        $cmd = ['git', 'stash', 'push'];
        if ($includeIgnored) {
            $cmd[] = '--all';
        } elseif ($includeUntracked) {
            $cmd[] = '--include-untracked';
        }

        if ($message) {
            $cmd[] = '-m';
            $cmd[] = $message;
        }

        return $this->runCommand($cmd, $path);
    }

    public function getStashList(string $path): array
    {
        // Format: stash@{0}|Subject|RelativeDate
        $result = $this->runCommand(['git', 'stash', 'list', '--pretty=format:%gd|%s|%ar'], $path);

        if (!$result['success']) return [];

        $lines = explode("\n", trim($result['output']));
        $stashes = [];

        foreach ($lines as $line) {
            if (empty($line)) continue;
            $parts = explode('|', $line, 3);
            if (count($parts) === 3) {
                $stashes[] = [
                    'index' => $parts[0], // e.g., stash@{0}
                    'message' => $parts[1],
                    'time' => $parts[2],
                ];
            }
        }

        return $stashes;
    }

    public function stashApply(string $path, string $stashRef): array
    {
        return $this->runCommand(['git', 'stash', 'apply', $stashRef], $path);
    }

    public function stashPop(string $path, string $stashRef): array
    {
        return $this->runCommand(['git', 'stash', 'pop', $stashRef], $path);
    }

    public function stashDrop(string $path, string $stashRef): array
    {
        return $this->runCommand(['git', 'stash', 'drop', $stashRef], $path);
    }

    /**
     * List all worktrees for a repository
     * 
     * @param string $path Path to the main repository
     * @return array Array of worktrees with path, branch, and head info
     */
    public function listWorktrees(string $path): array
    {
        $result = $this->runCommand(['git', 'worktree', 'list', '--porcelain'], $path);

        if (!$result['success']) {
            return [];
        }

        $lines = explode("\n", trim($result['output']));
        $worktrees = [];
        $current = [];

        foreach ($lines as $line) {
            $line = trim($line);
            
            if (empty($line)) {
                // Empty line separates worktrees
                if (!empty($current)) {
                    $worktrees[] = $current;
                    $current = [];
                }
                continue;
            }

            if (str_starts_with($line, 'worktree ')) {
                $current['path'] = substr($line, 9);
            } elseif (str_starts_with($line, 'HEAD ')) {
                $current['head'] = substr($line, 5);
            } elseif (str_starts_with($line, 'branch ')) {
                $branchRef = substr($line, 7);
                // Extract branch name from refs/heads/branch-name
                $current['branch'] = str_replace('refs/heads/', '', $branchRef);
            } elseif ($line === 'bare') {
                $current['bare'] = true;
            } elseif ($line === 'detached') {
                $current['detached'] = true;
                $current['branch'] = 'HEAD (detached)';
            }
        }

        // Add the last worktree if exists
        if (!empty($current)) {
            $worktrees[] = $current;
        }

        return $worktrees;
    }

    /**
     * Add a new worktree
     * 
     * @param string $repoPath Path to the main repository
     * @param string $worktreePath Path where the new worktree should be created
     * @param string $branch Branch name to checkout in the worktree
     * @param bool $createBranch Whether to create a new branch
     * @return array Command result
     */
    public function addWorktree(string $repoPath, string $worktreePath, string $branch, bool $createBranch = false): array
    {
        $cmd = ['git', 'worktree', 'add'];
        
        if ($createBranch) {
            $cmd[] = '-b';
            $cmd[] = $branch;
            $cmd[] = $worktreePath;
        } else {
            $cmd[] = $worktreePath;
            $cmd[] = $branch;
        }

        return $this->runCommand($cmd, $repoPath);
    }

    /**
     * Remove a worktree
     * 
     * @param string $worktreePath Path to the worktree to remove
     * @param bool $force Force removal even with uncommitted changes
     * @return array Command result
     */
    public function removeWorktree(string $worktreePath, bool $force = false): array
    {
        $cmd = ['git', 'worktree', 'remove'];
        
        if ($force) {
            $cmd[] = '--force';
        }
        
        $cmd[] = $worktreePath;

        // Run from the worktree's parent git directory
        // We need to find the .git directory
        $gitDir = dirname($worktreePath);
        while ($gitDir !== '/' && !is_dir($gitDir . '/.git')) {
            $gitDir = dirname($gitDir);
        }

        return $this->runCommand($cmd, $gitDir);
    }

    /**
     * Get the status of a worktree (check if it has uncommitted changes)
     * 
     * @param string $worktreePath Path to the worktree
     * @return array Status information with 'clean' boolean
     */
    public function getWorktreeStatus(string $worktreePath): array
    {
        $result = $this->runCommand(['git', 'status', '--porcelain'], $worktreePath);
        
        return [
            'success' => $result['success'],
            'clean' => $result['success'] && empty(trim($result['output'])),
            'output' => $result['output']
        ];
    }
}
