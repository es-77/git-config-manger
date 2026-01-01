<div class="h-full flex flex-col p-6 space-y-6">
    <div class="flex justify-between items-start">
        <div>
            <h1 class="text-2xl font-bold text-white">SSH Configuration</h1>
            <p class="text-gray-400 text-sm">Manage entries in your ~/.ssh/config file.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 flex-1 overflow-hidden">
        <!-- List -->
        <div class="lg:col-span-2 overflow-y-auto pr-2 space-y-3">
            @forelse($hosts as $host)
                <div
                    class="bg-gray-800 hover:bg-gray-750 border border-gray-700 rounded-xl p-4 group transition-all relative">
                    <div
                        class="absolute top-4 right-4 flex space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button wire:click="editHost('{{ $host['Host'] }}')"
                            class="text-gray-400 hover:text-indigo-400 p-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                </path>
                            </svg>
                        </button>
                        <button wire:click="deleteHost('{{ $host['Host'] }}')"
                            class="text-gray-400 hover:text-red-400 p-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                </path>
                            </svg>
                        </button>
                    </div>

                    <h3 class="text-lg font-bold text-indigo-400 mb-2">Host {{ $host['Host'] }}</h3>
                    <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-sm">
                        @foreach ($host['details'] as $key => $val)
                            <div class="text-gray-500 text-right">{{ $key }}:</div>
                            <div class="text-gray-300 font-mono truncate" title="{{ $val }}">
                                {{ $val }}</div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div
                    class="flex flex-col items-center justify-center h-48 text-gray-500 border border-dashed border-gray-700 rounded-xl">
                    <p>No SSH hosts found.</p>
                </div>
            @endforelse
        </div>

        <!-- Form -->
        <div class="bg-gray-800 border border-gray-700 rounded-xl p-5 h-fit shadow-lg">
            <h2 class="text-lg font-bold text-white mb-4">{{ $isEditing ? 'Edit Host' : 'Add New Host' }}</h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Host
                        Alias</label>
                    <input wire:model="alias" type="text" placeholder="e.g. github-work"
                        class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label
                        class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">HostName</label>
                    <input wire:model="hostName" type="text" placeholder="e.g. github.com"
                        class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label
                            class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">User</label>
                        <input wire:model="user" type="text" placeholder="git"
                            class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label
                            class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Port</label>
                        <input wire:model="port" type="text" placeholder="22"
                            class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Identity
                        File</label>
                    <div class="flex space-x-2">
                        <input wire:model="identityFile" type="text" placeholder="~/.ssh/id_rsa"
                            class="flex-1 bg-gray-900 border border-gray-600 rounded px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                        <button wire:click="pickIdentityFile"
                            class="bg-gray-700 hover:bg-gray-600 px-3 rounded text-white transition-colors">
                            ...
                        </button>
                    </div>
                </div>

                <div class="pt-2 flex space-x-3">
                    <button wire:click="saveHost"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white py-2 rounded-lg font-medium transition-colors shadow-lg">
                        {{ $isEditing ? 'Update Host' : 'Save Host' }}
                    </button>
                    @if ($isEditing)
                        <button wire:click="cancelEdit"
                            class="px-4 bg-gray-700 hover:bg-gray-600 text-white py-2 rounded-lg font-medium transition-colors">
                            Cancel
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
