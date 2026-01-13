<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class SshConfigService
{
    // Use user's home directory for parsing
    protected function getConfigPath(): string
    {
        // 1. Check for custom setting
        try {
            $customPath = \Native\Desktop\Facades\Settings::get('ssh_config_path');
            if ($customPath) {
                return $customPath;
            }
        } catch (\Throwable $e) {
            // Settings might not be available yet or failed
        }

        // 2. Default detection
        $home = getenv('HOME') ?: getenv('USERPROFILE') ?: $_SERVER['HOME'] ?? '';

        if (empty($home) && function_exists('posix_getpwuid')) {
            $home = posix_getpwuid(posix_getuid())['dir'];
        }

        return $home . DIRECTORY_SEPARATOR . '.ssh' . DIRECTORY_SEPARATOR . 'config';
    }

    public function getHosts(): array
    {
        $path = $this->getConfigPath();
        if (!file_exists($path)) {
            return [];
        }

        $content = file_get_contents($path);
        $lines = explode("\n", $content);
        $hosts = [];
        $currentHost = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '#')) {
                continue; // Skip comments and empty lines for now (simple parser)
            }

            if (str_starts_with(strtolower($line), 'host ')) {
                if ($currentHost) {
                    $hosts[] = $currentHost;
                }
                $currentHost = [
                    'Host' => trim(substr($line, 5)), // Get alias
                    'details' => []
                ];
            } elseif ($currentHost) {
                // Key Value pair
                $parts = preg_split('/\s+/', $line, 2);
                if (count($parts) === 2) {
                    $currentHost['details'][$parts[0]] = $parts[1];
                }
            }
        }
        if ($currentHost) {
            $hosts[] = $currentHost;
        }

        return $hosts;
    }

    public function saveHost(array $newHostData): bool
    {
        $hosts = $this->getHosts();

        // Check if updating existing
        $found = false;
        foreach ($hosts as &$host) {
            if ($host['Host'] === $newHostData['Host']) {
                $host['details'] = $newHostData['details'];
                $found = true;
                break;
            }
        }

        if (!$found) {
            $hosts[] = $newHostData;
        }

        return $this->writeHosts($hosts);
    }

    public function deleteHost(string $hostAlias): bool
    {
        $hosts = $this->getHosts();
        $hosts = array_filter($hosts, fn($h) => $h['Host'] !== $hostAlias);
        return $this->writeHosts($hosts);
    }

    protected function writeHosts(array $hosts): bool
    {
        $content = "";
        foreach ($hosts as $host) {
            $content .= "Host " . $host['Host'] . "\n";
            foreach ($host['details'] as $key => $value) {
                if (strtolower($key) === 'identityfile') {
                    $value = $this->contractPath($value);
                }
                $content .= "  $key $value\n";
            }
            $content .= "\n";
        }

        $path = $this->getConfigPath();

        // Ensure directory exists
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0700, true);
        }

        return file_put_contents($path, trim($content) . "\n") !== false;
    }

    public function normalizeConfig(): void
    {
        $hosts = $this->getHosts();
        $changed = false;

        foreach ($hosts as &$host) {
            if (isset($host['details']) && is_array($host['details'])) {
                foreach ($host['details'] as $key => &$value) {
                    if (strtolower($key) === 'identityfile') {
                        $normalized = $this->contractPath($value);
                        if ($normalized !== $value) {
                            $value = $normalized;
                            $changed = true;
                        }
                    }
                }
            }
        }

        if ($changed) {
            $this->writeHosts($hosts);
        }
    }

    public function expandPath(string $path): string
    {
        $home = getenv('HOME') ?: getenv('USERPROFILE') ?: $_SERVER['HOME'] ?? '';

        if (empty($home) && function_exists('posix_getpwuid')) {
            $home = posix_getpwuid(posix_getuid())['dir'];
        }

        if (str_starts_with($path, '~/') || str_starts_with($path, '~\\')) {
            return $home . DIRECTORY_SEPARATOR . substr($path, 2);
        }

        return $path;
    }

    public function contractPath(string $path): string
    {
        $home = getenv('HOME') ?: getenv('USERPROFILE') ?: $_SERVER['HOME'] ?? '';

        if (empty($home) && function_exists('posix_getpwuid')) {
            $home = posix_getpwuid(posix_getuid())['dir'];
        }

        if (str_starts_with($path, $home)) {
            $relativePath = substr($path, strlen($home) + 1);
            // Ensure forward slashes for SSH config compatibility on Windows
            $relativePath = str_replace('\\', '/', $relativePath);
            return '~/' . $relativePath;
        }

        // Also normalize non-contracted paths to forward slashes
        return $this->normalizePath($path);
    }

    public function normalizePath(string $path): string
    {
        return str_replace('\\', '/', $path);
    }
}
