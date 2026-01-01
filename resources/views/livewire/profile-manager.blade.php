<div class="h-full flex flex-col p-6 space-y-6" x-data
    @confirm-git-init.window="if(confirm('Selected directory is not a git repository. Initialize it now?')) { $wire.initializeAndApply($event.detail.name, $event.detail.email); }">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-white">Git Identities</h1>
            <p class="text-gray-400 text-sm">Manage your git user profiles for global and local use.</p>
        </div>

        <!-- Add Profile Form (Inline) -->
        <!-- Add/Edit Profile Form (Inline) -->
        <div class="bg-gray-800 p-3 rounded-lg flex items-center space-x-2 border border-gray-700 shadow-sm">
            <input wire:model="name" type="text" placeholder="Name"
                class="bg-gray-900 border border-gray-600 rounded px-3 py-1.5 text-sm text-white focus:outline-none focus:border-indigo-500 w-32">
            <input wire:model="email" type="text" placeholder="Email"
                class="bg-gray-900 border border-gray-600 rounded px-3 py-1.5 text-sm text-white focus:outline-none focus:border-indigo-500 w-48">

            @if ($editingProfileId)
                <button wire:click="cancelEdit"
                    class="bg-gray-600 hover:bg-gray-500 text-white px-3 py-1.5 rounded text-sm font-medium transition-colors">
                    Cancel
                </button>
                <button wire:click="saveProfile"
                    class="bg-yellow-600 hover:bg-yellow-500 text-white px-3 py-1.5 rounded text-sm font-medium transition-colors">
                    Update
                </button>
            @else
                <button wire:click="saveProfile"
                    class="bg-indigo-600 hover:bg-indigo-500 text-white px-3 py-1.5 rounded text-sm font-medium transition-colors">
                    Add
                </button>
            @endif
        </div>
    </div>

    <!-- Directory Picker Section -->
    <div class="grid gap-4">
        <div class="bg-gray-800 rounded-xl p-4 border border-gray-700 shadow-sm flex items-center justify-between">
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Current Active
                    Identity</label>
                <div class="flex items-center space-x-2">
                    <span class="text-white font-medium">{{ $currentConfig['name'] }}</span>
                    <span class="text-gray-400 text-sm">&lt;{{ $currentConfig['email'] }}&gt;</span>
                </div>
                <div class="text-xs text-indigo-400 mt-1 uppercase tracking-wide font-bold">
                    Scope: {{ $currentConfig['scope'] }}
                </div>
                @if (!empty($currentConfig['remoteUrl']))
                    <div class="text-xs text-gray-500 mt-1 truncate max-w-md" title="{{ $currentConfig['remoteUrl'] }}">
                        <span class="uppercase tracking-wide font-bold text-gray-600">Origin:</span>
                        {{ $currentConfig['remoteUrl'] }}
                    </div>
                @endif
            </div>
            <div class="text-gray-500">
                <svg class="w-8 h-8 opacity-20" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                        clip-rule="evenodd" />
                </svg>
            </div>
        </div>

        <div class="bg-gray-800 rounded-xl p-4 border border-gray-700 shadow-sm">
            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Target Directory (for
                Local Config)</label>
            <div class="flex space-x-2">
                <div
                    class="flex-1 bg-gray-900 border border-gray-600 rounded-lg px-4 py-2 text-gray-300 flex items-center truncate">
                    {{ $directory ?: 'No folder selected' }}
                </div>
                <button wire:click="pickDirectory"
                    class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                    </svg>
                    <span>Browse...</span>
                </button>
            </div>
        </div>

        @if (str_contains($currentConfig['scope'], 'Local'))
            <div class="bg-gray-800 rounded-xl p-4 border border-gray-700 shadow-sm">
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Remote Origin
                    (Origin URL)</label>
                <div class="flex space-x-2">
                    <input wire:model="remoteUrlInput" type="text"
                        class="flex-1 bg-gray-900 border border-gray-600 rounded-lg px-4 py-2 text-gray-300 focus:outline-none focus:border-indigo-500 text-sm font-mono"
                        placeholder="git@github.com:user/repo.git">
                    <button wire:click="updateRemoteUrl"
                        class="bg-gray-700 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap">
                        Update Remote
                    </button>
                </div>
            </div>
        @endif
    </div>

    <!-- Profiles List -->
    <div class="flex-1 overflow-y-auto pr-2 space-y-3">
        @forelse($profiles as $profile)
            <div
                class="bg-gray-800 hover:bg-gray-750 border border-gray-700 rounded-xl p-4 flex items-center justify-between group transition-all">
                <div class="flex items-center space-x-4">
                    <div
                        class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg">
                        {{ strtoupper(substr($profile['name'], 0, 1)) }}
                    </div>
                    <div>
                        <div class="font-semibold text-white">{{ $profile['name'] }}</div>
                        <div class="text-sm text-gray-400">{{ $profile['email'] }}</div>
                    </div>
                </div>

                <div class="flex items-center space-x-2 opacity-100 transition-opacity">
                    <!-- Apply Global -->
                    <button wire:click="applyGlobal('{{ $profile['name'] }}', '{{ $profile['email'] }}')"
                        class="text-xs bg-gray-700 hover:bg-indigo-600 text-gray-300 hover:text-white px-3 py-1.5 rounded transition-colors"
                        title="Apply Global Config">
                        Set Global
                    </button>

                    <!-- Apply Local -->
                    <button wire:click="applyLocal('{{ $profile['name'] }}', '{{ $profile['email'] }}')"
                        class="text-xs bg-gray-700 hover:bg-green-600 text-gray-300 hover:text-white px-3 py-1.5 rounded transition-colors {{ !$directory ? 'opacity-50 cursor-not-allowed' : '' }}"
                        {{ !$directory ? 'disabled' : '' }} title="Apply to Directory">
                        Set Local
                    </button>

                    <!-- Edit -->
                    <button wire:click="editProfile('{{ $profile['id'] }}')"
                        class="text-gray-500 hover:text-yellow-400 p-2 rounded transition-colors" title="Edit Profile">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                            </path>
                        </svg>
                    </button>

                    <!-- Delete -->
                    <button wire:click="deleteProfile('{{ $profile['id'] }}')"
                        class="text-gray-500 hover:text-red-400 p-2 rounded transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                            </path>
                        </svg>
                    </button>
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center h-48 text-gray-500">
                <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                    </path>
                </svg>
                <p>No profiles found. Add one above.</p>
            </div>
        @endforelse
    </div>
</div>
