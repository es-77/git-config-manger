<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class SshConfigService
{
    // Use user's home directory for parsing
    protected function getConfigPath(): string
    {
        $home = getenv('HOME') ?: $_SERVER['HOME'] ?? '';

        if (empty($home) && function_exists('posix_getpwuid')) {
            $home = posix_getpwuid(posix_getuid())['dir'];
        }

        return $home . '/.ssh/config';
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
}
