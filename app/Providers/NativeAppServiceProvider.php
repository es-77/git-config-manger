<?php

namespace App\Providers;

use Native\Desktop\Facades\Window;
use Native\Desktop\Facades\GlobalShortcut;
use Native\Desktop\Contracts\ProvidesPhpIni;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        Window::open()
            ->width(1000)
            ->height(800)
            ->title('Git Config Manager (Emmanuel saleem)')
            ->rememberState()
            ->frame(false)
            ->icon(resource_path('logo.svg'));

        GlobalShortcut::new()
            ->key('CmdOrCtrl+L')
            ->event(\App\Events\OpenSpotlight::class)
            ->register();

        \Illuminate\Support\Facades\Event::listen(\App\Events\OpenSpotlight::class, function () {
            Window::open('spotlight')
                ->route('spotlight')
                ->width(600)
                ->height(400)
                ->showDevTools(false)
                ->focusable(true)
                ->alwaysOnTop(true)
                ->frame(false)
                ->transparent(true)
                ->resizable(false);
        });
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [];
    }
}
