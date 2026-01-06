<div class="h-full w-full overflow-y-auto p-6 space-y-6">
    <!-- Full Page Loading Overlay -->
    <div wire:loading.flex wire:target="runCloneCommand" style="display: none;"
        class="fixed inset-0 z-50 bg-black/80 flex-col items-center justify-center backdrop-blur-sm transition-opacity">
        <div class="flex flex-col items-center p-8 bg-gray-800 rounded-xl border border-gray-700 shadow-2xl space-y-4">
            <svg class="animate-spin w-12 h-12 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
            <div class="text-center">
                <h3 class="text-lg font-bold text-white">Cloning Repository</h3>
                <p class="text-gray-400 text-sm mt-1">Please wait while the project is being set up...</p>
            </div>
        </div>
    </div>
    <!-- Remote Origin Loading Overlay -->
    <div wire:loading.flex wire:target="runOriginCommand" style="display: none;"
        class="fixed inset-0 z-50 bg-black/80 flex-col items-center justify-center backdrop-blur-sm transition-opacity">
        <div class="flex flex-col items-center p-8 bg-gray-800 rounded-xl border border-gray-700 shadow-2xl space-y-4">
            <svg class="animate-spin w-12 h-12 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
            <div class="text-center">
                <h3 class="text-lg font-bold text-white">Configuring Remote</h3>
                <p class="text-gray-400 text-sm mt-1">Initializing, setting origin, and pushing...</p>
            </div>
        </div>
    </div>
    <div class="flex justify-between items-start">
        <div>
            <h1 class="text-2xl font-bold text-white">Project Setup</h1>
            <p class="text-gray-400 text-sm mt-1">Clone repositories or initialize existing projects</p>
        </div>
    </div>

    <!-- Grid Layout for Side-by-Side Cards -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        <!-- Clone Helper Section -->
        <div class="bg-gray-800 border border-gray-700 rounded-xl p-5 shadow-lg flex flex-col h-full">
            <h2 class="text-lg font-bold text-white mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z">
                    </path>
                </svg>
                Clone Helper
            </h2>
            <div class="space-y-4 flex-1">
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Repository
                        URL (Any Format)</label>
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

                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Current
                        Active Identity</label>
                    <select wire:model.live="cloneSelectedProfile"
                        class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                        <option value="">Do Not Set Local Config</option>
                        @foreach ($profiles as $profile)
                            <option value="{{ $profile['id'] }}">{{ $profile['name'] }} <{{ $profile['email'] }}>
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
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

                @if ($this->cloneCommand)
                    <div
                        class="mt-4 p-3 bg-gray-900 border border-gray-600 rounded-lg flex items-center justify-between group">
                        <code class="text-green-400 font-mono text-sm break-all mr-2">{{ $this->cloneCommand }}</code>
                        <div class="flex items-center shrink-0">
                            <button wire:click="runCloneCommand" wire:loading.attr="disabled"
                                class="text-indigo-400 hover:text-indigo-300 transition-colors flex items-center disabled:opacity-50 disabled:cursor-not-allowed"
                                title="Run Command">
                                <span>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z">
                                        </path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
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

        <!-- Remote Origin Helper Section -->
        <div class="bg-gray-800 border border-gray-700 rounded-xl p-5 shadow-lg flex flex-col h-full">
            <h2 class="text-lg font-bold text-white mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                </svg>
                Remote Origin Helper
            </h2>
            <div class="space-y-4 flex-1">
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Repository
                        URL (Any Format)</label>
                    <input wire:model.live="originInputUrl" type="text" placeholder="git@github.com:user/repo.git"
                        class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Use Host
                        Alias</label>
                    <select wire:model.live="originSelectedHost"
                        class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                        <option value="">Select a Host...</option>
                        @foreach ($hosts as $host)
                            <option value="{{ $host['Host'] }}">{{ $host['Host'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Current
                        Active Identity</label>
                    <select wire:model.live="originSelectedProfile"
                        class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                        <option value="">Do Not Set Local Config</option>
                        @foreach ($profiles as $profile)
                            <option value="{{ $profile['id'] }}">{{ $profile['name'] }} <{{ $profile['email'] }}>
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Project
                        Directory (Where to run commands)</label>
                    <div class="flex space-x-2">
                        <input wire:model.live="originProjectDirectory" type="text"
                            placeholder="Folder where your project is..."
                            class="flex-1 bg-gray-900 border border-gray-600 rounded px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                        <button wire:click="pickOriginProjectDirectory"
                            class="bg-gray-700 hover:bg-gray-600 px-3 rounded text-white transition-colors">
                            Browse...
                        </button>
                    </div>
                </div>

                @if ($this->originCommand)
                    <div
                        class="mt-4 p-3 bg-gray-900 border border-gray-600 rounded-lg flex items-center justify-between group">
                        <code
                            class="text-green-400 font-mono text-sm break-all mr-2">{{ $this->originCommand }}</code>
                        <div class="flex items-center shrink-0">
                            <button wire:click="runOriginCommand" wire:loading.attr="disabled"
                                class="text-indigo-400 hover:text-indigo-300 transition-colors flex items-center disabled:opacity-50 disabled:cursor-not-allowed"
                                title="Run Command">
                                <span>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z">
                                        </path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </span>
                            </button>
                            <button wire:click="copyOriginCommand"
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
    </div>
</div>
<script>
    window.addEventListener('copy-to-clipboard', event => {
        navigator.clipboard.writeText(event.detail.content).catch(err => {
            console.error('Failed to copy text: ', err);
        });
    });
</script>
