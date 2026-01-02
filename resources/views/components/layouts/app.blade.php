<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Git Manager' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom Scrollbar for Webkit */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background-color: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background-color: rgba(156, 163, 175, 0.5);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background-color: rgba(107, 114, 128, 0.8);
        }
    </style>
</head>

<body
    class="bg-gray-900 text-gray-100 font-sans antialiased h-screen flex flex-col overflow-hidden selection:bg-indigo-500 selection:text-white">

    <!-- Custom Title Bar -->
    <header
        class="h-10 bg-gray-800 flex items-center justify-between px-3 select-none draggable-region border-b border-gray-700"
        style="-webkit-app-region: drag">
        <div class="flex items-center space-x-2">
            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
            </svg>
            <span class="text-sm font-medium">Git Config Manager</span>
        </div>
        <div class="flex items-center space-x-1 no-drag" style="-webkit-app-region: no-drag">
            <!-- Minimize -->
            <button onclick="handleWindowAction('minimize')"
                class="p-1.5 hover:bg-gray-700 rounded transition-colors text-gray-400 hover:text-white focus:outline-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                </svg>
            </button>
            <!-- Maximize/Restore -->
            <button onclick="handleWindowAction('maximize')"
                class="p-1.5 hover:bg-gray-700 rounded transition-colors text-gray-400 hover:text-white focus:outline-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4">
                    </path>
                </svg>
            </button>
            <!-- Close -->
            <button onclick="handleWindowAction('close')"
                class="p-1.5 hover:bg-red-600 rounded transition-colors text-gray-400 hover:text-white focus:outline-none group">
                <svg class="w-4 h-4 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>
    </header>

    <div class="flex-1 flex overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-800 border-r border-gray-700 flex flex-col">
            <!-- Updated Sidebar Header to remove redundancy or keep as brand area -->
            <div class="p-4 border-b border-gray-700 flex items-center space-x-2">
                <span class="font-bold text-lg tracking-wide">GitConfig<span class="text-indigo-500">Mgr</span></span>
            </div>

            <nav class="flex-1 overflow-y-auto p-4 space-y-2">
                <a href="/" wire:navigate
                    class="flex items-center space-x-3 px-3 py-2 rounded-lg transition-colors {{ request()->is('/') ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span>Identities</span>
                </a>
                <a href="/ssh" wire:navigate
                    class="flex items-center space-x-3 px-3 py-2 rounded-lg transition-colors {{ request()->is('ssh') ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                        </path>
                    </svg>
                    <span>SSH Keys</span>
                </a>
                <a href="/git-ops" wire:navigate
                    class="flex items-center space-x-3 px-3 py-2 rounded-lg transition-colors {{ request()->is('git-ops') ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                    <span>Git Operations</span>
                </a>
                <a href="/project-setup" wire:navigate
                    class="flex items-center space-x-3 px-3 py-2 rounded-lg transition-colors {{ request()->is('project-setup') ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Project Setup</span>
                </a>
            </nav>

            <div class="p-4 border-t border-gray-700 text-xs text-gray-500 text-center">
                Press <span class="bg-gray-700 px-1 py-0.5 rounded text-gray-300">Cmd+L</span> to quick switch
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col overflow-hidden bg-gray-900 relative">
            {{ $slot }}
        </main>
    </div>

    <!-- Toast/Notification Container -->
    <div x-data="{
        notifications: [],
        add(msg) {
            this.notifications.push({ id: Date.now(), msg: msg });
            setTimeout(() => { this.remove(this.notifications[this.notifications.length - 1].id) }, 3000);
        },
        remove(id) {
            this.notifications = this.notifications.filter(n => n.id !== id);
        }
    }" @notify.window="add($event.detail)"
        class="fixed bottom-4 right-4 z-50 flex flex-col space-y-2 pointer-events-none">

        <template x-for="notif in notifications" :key="notif.id">
            <div class="bg-gray-800 border-l-4 border-indigo-500 text-white px-4 py-3 rounded shadow-lg transform transition-all duration-300 pointer-events-auto flex items-center min-w-[300px]"
                x-transition:enter="translate-y-2 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
                x-transition:leave="opacity-0 scale-90">
                <span x-text="notif.msg"></span>
            </div>
        </template>
    </div>

    <script>
        // Get CSRF token from meta tag
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        async function handleWindowAction(action) {
            try {
                const response = await fetch(`/native/${action}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    console.error(`Failed to ${action} window:`, response.statusText);
                }
            } catch (error) {
                console.error(`Error executing ${action}:`, error);
            }
        }
    </script>

    @livewireScripts
</body>

</html>
