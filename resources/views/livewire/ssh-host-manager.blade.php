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
                    <input wire:model="hostName" type="text" placeholder="e.g. github.com" list="hostnames"
                        class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                    <datalist id="hostnames">
                        <option value="github.com">
                        <option value="gitlab.com">
                        <option value="bitbucket.org">
                        <option value="ssh.dev.azure.com">
                    </datalist>
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
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Preferred
                        Authentications</label>
                    <input wire:model="preferredAuthentications" type="text" placeholder="publickey"
                        list="auth_methods"
                        class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                    <datalist id="auth_methods">
                        <option value="publickey">
                        <option value="password">
                        <option value="keyboard-interactive">
                    </datalist>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Identity
                        File</label>
                    <div class="flex space-x-2">
                        <input wire:model="identityFile" type="text" placeholder="~/.ssh/id_rsa"
                            class="flex-1 bg-gray-900 border border-gray-600 rounded px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                        <button wire:click="pickIdentityFile" title="Browse"
                            class="bg-gray-700 hover:bg-gray-600 px-3 rounded text-white transition-colors">
                            ...
                        </button>
                        <button wire:click="copyPublicKey" title="Copy Public Key"
                            class="bg-gray-700 hover:bg-blue-600 px-3 rounded text-white transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3">
                                </path>
                            </svg>
                        </button>
                        <button @click="$dispatch('open-modal', 'key-gen-modal')" title="Create New Key"
                            class="bg-gray-700 hover:bg-green-600 px-3 rounded text-white transition-colors pb-1 text-lg font-bold">
                            +
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

    <!-- Key Gen Modal -->
    <div x-data="{ open: false, filename: 'id_ed25519' }" @open-modal.window="if ($event.detail === 'key-gen-modal') open = true"
        @close-modal.window="if ($event.detail === 'key-gen-modal') open = false" x-show="open"
        style="display: none;"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

        <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 shadow-2xl w-full max-w-sm"
            @click.away="open = false">
            <h3 class="text-xl font-bold text-white mb-4">Generate New SSH Key</h3>
            <p class="text-gray-400 text-sm mb-4">Generate a new ED25519 SSH key in your ~/.ssh directory.</p>

            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Key
                    Filename</label>
                <input x-model="filename" type="text"
                    class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500"
                    @keydown.enter="$wire.generateNewKey(filename)">
            </div>

            <div class="flex justify-end space-x-3">
                <button @click="open = false"
                    class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded text-sm transition-colors">
                    Cancel
                </button>
                <button @click="$wire.generateNewKey(filename)"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded text-sm font-medium transition-colors">
                    Generate & Select
                </button>
            </div>
        </div>
    </div>
    <!-- Clone Helper Section -->
    <div class="mt-6 bg-gray-800 border border-gray-700 rounded-xl p-5 shadow-lg">
        <h2 class="text-lg font-bold text-white mb-4">Clone Helper</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Repository URL
                    (Any Format)</label>
                <input wire:model.live="cloneInputUrl" type="text" placeholder="git@github.com:user/repo.git"
                    class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Use Host
                    Alias</label>
                <select wire:model.live="cloneSelectedHost"
                    class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                    <option value="">Select a Host...</option>
                    @foreach ($hosts as $host)
                        <option value="{{ $host['Host'] }}">{{ $host['Host'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Target Clone
                    Directory (Optional)</label>
                <div class="flex space-x-2">
                    <input wire:model.live="cloneTargetDirectory" type="text"
                        placeholder="Folder where to run git clone command..."
                        class="flex-1 bg-gray-900 border border-gray-600 rounded px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                    <button wire:click="pickCloneTargetDirectory"
                        class="bg-gray-700 hover:bg-gray-600 px-3 rounded text-white transition-colors">
                        Browse...
                    </button>
                </div>
            </div>
        </div>

        @if ($this->cloneCommand)
            <div
                class="mt-4 p-3 bg-gray-900 border border-gray-600 rounded-lg flex items-center justify-between group">
                <code class="text-green-400 font-mono text-sm break-all">{{ $this->cloneCommand }}</code>
                <div class="flex items-center">
                    <button wire:click="runCloneCommand" wire:loading.attr="disabled"
                        class="ml-3 text-indigo-400 hover:text-indigo-300 transition-colors flex items-center"
                        title="Run Command">
                        <span wire:loading.remove wire:target="runCloneCommand">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </span>
                        <span wire:loading wire:target="runCloneCommand">
                            <svg class="animate-spin w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                        </span>
                    </button>
                    <button wire:click="copyCloneCommand"
                        class="ml-3 text-gray-400 hover:text-white transition-colors" title="Copy Command">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3">
                            </path>
                        </svg>
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>
<script>
    window.addEventListener('copy-to-clipboard', event => {
        navigator.clipboard.writeText(event.detail.content).catch(err => {
            console.error('Failed to copy text: ', err);
        });
    });
</script>
