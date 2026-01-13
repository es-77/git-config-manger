<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\ProfileManager;
use App\Livewire\SshHostManager;
use App\Livewire\GitCommandManager;
use App\Livewire\ProjectSetup;
use App\Livewire\Spotlight;
use Native\Desktop\Facades\Window;

Route::get('/', ProfileManager::class);
Route::get('/ssh', SshHostManager::class);
Route::get('/git-ops', GitCommandManager::class);
Route::get('/project-setup', ProjectSetup::class);
Route::get('/spotlight', Spotlight::class);
Route::get('/logs', \App\Livewire\LogViewer::class);

Route::post('/native/minimize', function () {
    Window::minimize();
});

Route::post('/native/maximize', function () {
    Window::maximize();
});

Route::post('/native/close', function () {
    Window::close();
});
