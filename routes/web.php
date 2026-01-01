<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\ProfileManager;
use App\Livewire\SshHostManager;
use App\Livewire\Spotlight;

Route::get('/', ProfileManager::class);
Route::get('/ssh', SshHostManager::class);
Route::get('/spotlight', Spotlight::class);
