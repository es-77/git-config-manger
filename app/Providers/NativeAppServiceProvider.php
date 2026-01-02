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
            ->rememberState();

        GlobalShortcut::new()
            ->key('CmdOrCtrl+L')
            ->event(\App\Events\OpenSpotlight::class)
            ->register();

        // Register event listener or just handle the window opening directly if possible
        // Ideally we should use an Event Listener for the shortcut, but for simplicity we can register it here
        // However, GlobalShortcut usually dispatches an event.
        // Let's create the event class and listener, OR just try to open it directly if the API supports it.
        // Checking NativePHP docs (implied): Shortcuts dispatch events.

        // Let's manually register the listener for the event we just defined above
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
