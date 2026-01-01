<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <div class="flex-1 flex overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-800 border-r border-gray-700 flex flex-col">
            <div class="p-4 border-b border-gray-700 flex items-center space-x-2 draggable-region">
                <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                </svg>
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

    @livewireScripts
</body>

</html>
