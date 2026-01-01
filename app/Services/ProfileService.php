<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class ProfileService
{
    protected string $path = 'profiles.json';

    public function getProfiles(): array
    {
        if (!Storage::exists($this->path)) {
            return [];
        }
        $json = Storage::get($this->path);
        return json_decode($json, true) ?? [];
    }

    public function addProfile(string $name, string $email): void
    {
        $profiles = $this->getProfiles();
        $profiles[] = [
            'id' => uniqid(),
            'name' => $name,
            'email' => $email,
            'created_at' => now()->toIso8601String()
        ];
        $this->saveProfiles($profiles);
    }

    public function updateProfile(string $id, string $name, string $email): void
    {
        $profiles = $this->getProfiles();
        foreach ($profiles as &$profile) {
            if ($profile['id'] === $id) {
                $profile['name'] = $name;
                $profile['email'] = $email;
                $profile['updated_at'] = now()->toIso8601String();
                break;
            }
        }
        $this->saveProfiles($profiles);
    }

    public function deleteProfile(string $id): void
    {
        $profiles = $this->getProfiles();
        $profiles = array_filter($profiles, fn($p) => $p['id'] !== $id);
        $this->saveProfiles(array_values($profiles));
    }

    protected function saveProfiles(array $profiles): void
    {
        Storage::put($this->path, json_encode($profiles, JSON_PRETTY_PRINT));
    }
}
