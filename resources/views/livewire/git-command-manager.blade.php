<div class="h-full flex flex-col p-6 space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-white">Git Operations</h1>
            <p class="text-gray-400 text-sm">Execute commands and view logs for your repository.</p>
        </div>

        <!-- Branch/Directory Display -->
        <div class="flex space-x-4 w-2/3 justify-end items-center">

            @if ($currentBranch)
                <div class="relative bg-gray-800 border border-indigo-500/30 rounded-lg max-w-[200px]"
                    title="Current Branch: {{ $currentBranch }}">
                    <div class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <select wire:change="switchBranch($event.target.value)"
                        class="appearance-none bg-transparent text-indigo-300 font-mono text-sm font-bold pl-8 pr-4 py-1.5 w-full rounded-lg focus:outline-none focus:bg-gray-700 cursor-pointer text-ellipsis overflow-hidden">
                        @foreach ($branches as $branch)
                            <option value="{{ $branch }}" {{ $branch === $currentBranch ? 'selected' : '' }}>
                                {{ $branch }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 text-gray-300 flex items-center min-w-0 max-w-xs"
                title="{{ $directory ?: 'No repository selected' }}">
                <span class="text-xs font-semibold text-gray-500 mr-2 uppercase whitespace-nowrap">Repo:</span>
                <span class="truncate block font-mono text-xs">{{ $directory ? basename($directory) : 'None' }}</span>
            </div>

            <button wire:click="pickDirectory"
                class="bg-gray-700 hover:bg-gray-600 text-white px-3 py-2 rounded-lg text-sm font-medium transition-colors flex items-center space-x-2 whitespace-nowrap shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                </svg>
                <span>Open</span>
            </button>
        </div>
    </div>

    <div class="flex-1 flex space-x-6 overflow-hidden">
        <!-- Left Panel: Actions -->
        <div class="w-64 flex flex-col space-y-3 overflow-y-auto">
            <h3 class="text-gray-400 text-xs font-bold uppercase tracking-wider">Common Actions</h3>

            <button wire:click="gitPull" wire:loading.attr="disabled"
                class="w-full text-left px-4 py-3 rounded-lg bg-gray-800 hover:bg-gray-700 transition-colors flex items-center justify-between group border border-gray-700">
                <span class="text-sm font-semibold text-gray-200">Pull</span>
                <svg class="w-4 h-4 text-gray-400 group-hover:text-white" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4-4-4m4 5V4"></path>
                </svg>
            </button>

            <button wire:click="gitPush" wire:loading.attr="disabled"
                class="w-full text-left px-4 py-3 rounded-lg bg-gray-800 hover:bg-gray-700 transition-colors flex items-center justify-between group border border-gray-700">
                <span class="text-sm font-semibold text-gray-200">Push</span>
                <svg class="w-4 h-4 text-gray-400 group-hover:text-white" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m-4-4v12"></path>
                </svg>
            </button>

            <button wire:click="gitFetch" wire:loading.attr="disabled"
                class="w-full text-left px-4 py-3 rounded-lg bg-gray-800 hover:bg-gray-700 transition-colors flex items-center justify-between group border border-gray-700">
                <span class="text-sm font-semibold text-gray-200">Fetch</span>
                <svg class="w-4 h-4 text-gray-400 group-hover:text-white" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                    </path>
                </svg>
            </button>

            <button wire:click="gitLog" wire:loading.attr="disabled"
                class="w-full text-left px-4 py-3 rounded-lg bg-gray-800 hover:bg-gray-700 transition-colors flex items-center justify-between group border border-gray-700">
                <span class="text-sm font-semibold text-gray-200">History</span>
                <svg class="w-4 h-4 text-gray-400 group-hover:text-white" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </button>

            <div class="h-4"></div>
            <h3 class="text-gray-400 text-xs font-bold uppercase tracking-wider">Destructive Actions</h3>

            <button wire:click="gitRollback"
                wire:confirm="ROLLBACK (Mixed Reset): Undo last commit, but KEEP changes? Valid for local commits."
                wire:loading.attr="disabled"
                class="w-full text-left px-4 py-3 rounded-lg bg-gray-800 hover:bg-yellow-900 border border-gray-700 hover:border-yellow-700 transition-colors flex items-center justify-between group">
                <span class="text-sm font-semibold text-gray-200">Rollback</span>
                <svg class="w-4 h-4 text-gray-400 group-hover:text-yellow-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                </svg>
            </button>

            <button wire:click="gitSoftReset"
                wire:confirm="SOFT RESET: Undo last commit, keep changes STAGED? Valid for local commits."
                wire:loading.attr="disabled"
                class="w-full text-left px-4 py-3 rounded-lg bg-gray-800 hover:bg-yellow-900 border border-gray-700 hover:border-yellow-700 transition-colors flex items-center justify-between group">
                <span class="text-sm font-semibold text-gray-200">Soft Reset</span>
                <svg class="w-4 h-4 text-gray-400 group-hover:text-yellow-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0019 16V8a1 1 0 00-1.6-.8l-5.333 4zM4.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0011 16V8a1 1 0 00-1.6-.8l-5.334 4z">
                    </path>
                </svg>
            </button>

            <button wire:click="gitHardReset"
                wire:confirm="HARD RESET: WARNING! DELETE all uncommitted changes and reset to last commit? This CANNOT be undone."
                wire:loading.attr="disabled"
                class="w-full text-left px-4 py-3 rounded-lg bg-gray-800 hover:bg-red-900 border border-gray-700 hover:border-red-700 transition-colors flex items-center justify-between group">
                <span class="text-sm font-semibold text-gray-200">Hard Reset</span>
                <svg class="w-4 h-4 text-gray-400 group-hover:text-red-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                    </path>
                </svg>
            </button>

            <div wire:loading class="text-center text-xs text-indigo-400 animate-pulse mt-4">
                Working on it...
            </div>
        </div>

        <!-- Right Panel: Output Log / History -->
        <div class="flex-1 bg-black rounded-lg border border-gray-700 overflow-hidden flex flex-col relative">
            <!-- Tabs -->
            <div class="flex bg-gray-800 border-b border-gray-700">
                <button wire:click="showConsole"
                    class="px-6 py-2 text-sm font-medium transition-colors border-r border-gray-700 {{ $viewMode === 'console' ? 'bg-gray-700 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-700/50' }}">
                    Terminal
                </button>
                <button wire:click="showHistory"
                    class="px-6 py-2 text-sm font-medium transition-colors border-r border-gray-700 {{ $viewMode === 'history' ? 'bg-gray-700 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-700/50' }}">
                    History
                </button>

                @if ($viewMode === 'console')
                    <div class="ml-auto px-4 flex items-center">
                        <button wire:click="clearLog"
                            class="text-xs text-gray-500 hover:text-white hover:underline">Clear</button>
                    </div>
                @endif
            </div>

            <div class="flex-1 overflow-hidden relative">
                @if ($viewMode === 'console')
                    <!-- Console View -->
                    <div class="absolute inset-0 p-4 overflow-y-auto font-mono text-sm space-y-1"
                        id="terminal-output">
                        @forelse($outputLog as $log)
                            <div class="break-words">
                                <span class="text-gray-600">[{{ $log['time'] }}]</span>
                                <span class="{{ $log['color'] }}">{!! nl2br(e($log['message'])) !!}</span>
                            </div>
                        @empty
                            <div class="text-gray-600 italic text-center mt-10">No commands run yet defined.</div>
                        @endforelse
                        <div x-data x-init="$el.scrollIntoView()"></div>
                    </div>
                @elseif($viewMode === 'history')
                    <!-- History List View -->
                    <div class="absolute inset-0 overflow-y-auto">
                        @forelse($history as $commit)
                            <div class="border-b border-gray-800 transition-colors group">
                                <div wire:click="expandCommit('{{ $commit['hash'] }}')"
                                    class="flex items-start p-3 hover:bg-gray-800/50 cursor-pointer">
                                    <div class="flex-shrink-0 mr-3 mt-1">
                                        <!-- Avatar Placeholder (Initials) -->
                                        <div
                                            class="w-8 h-8 rounded-full bg-gray-700 flex items-center justify-center text-xs font-bold text-gray-300 border border-gray-600">
                                            {{ strtoupper(substr($commit['author_name'], 0, 2)) }}
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex justify-between items-baseline mb-0.5">
                                            <p class="text-sm font-semibold text-gray-200 truncate pr-2"
                                                title="{{ $commit['message'] }}">
                                                {{ $commit['message'] }}
                                            </p>
                                            <span
                                                class="text-xs text-gray-500 whitespace-nowrap">{{ $commit['time'] }}</span>
                                        </div>
                                        <div class="flex justify-between items-center text-xs">
                                            <div class="flex items-center space-x-2">
                                                <span class="text-gray-400">{{ $commit['author_name'] }}</span>
                                                @if (isset($commit['author_email']))
                                                    <span
                                                        class="text-gray-600 hidden group-hover:inline">&lt;{{ $commit['author_email'] }}&gt;</span>
                                                @endif
                                            </div>
                                            <div
                                                class="font-mono text-gray-600 bg-gray-900 px-1.5 py-0.5 rounded text-[10px] border border-gray-800">
                                                {{ $commit['hash'] }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Expanded Details -->
                                @if ($selectedCommitHash === $commit['hash'])
                                    <div class="bg-black/30 px-3 pb-3 pt-1 border-t border-gray-800/50">
                                        <div
                                            class="text-xs font-semibold text-gray-500 mb-2 uppercase tracking-wider pl-11">
                                            Changed Files</div>
                                        <div class="space-y-1 pl-11">
                                            @forelse($commitDetails as $file)
                                                <div wire:click="showFileDiff('{{ $file['file'] }}')"
                                                    class="flex items-center text-xs font-mono group/file cursor-pointer hover:bg-gray-800/50 rounded px-1 transition-colors">
                                                    @php
                                                        $statusColors = [
                                                            'added' => 'text-green-400',
                                                            'modified' => 'text-yellow-400',
                                                            'deleted' => 'text-red-400',
                                                            'renamed' => 'text-purple-400',
                                                            'unknown' => 'text-gray-400',
                                                        ];
                                                        $statusIcons = [
                                                            'added' => '+',
                                                            'modified' => '•',
                                                            'deleted' => '-',
                                                            'renamed' => '→',
                                                            'unknown' => '?',
                                                        ];
                                                        $color = $statusColors[$file['status']] ?? 'text-gray-400';
                                                        $icon = $statusIcons[$file['status']] ?? '?';
                                                    @endphp
                                                    <span
                                                        class="{{ $color }} w-4 text-center font-bold mr-2">{{ $icon }}</span>
                                                    <span
                                                        class="text-gray-300 group-hover/file:text-white truncate underline group-hover/file:no-underline">{{ $file['file'] }}</span>
                                                    <span
                                                        class="ml-auto text-[10px] uppercase text-gray-600 ml-2">{{ $file['status'] }}</span>
                                                </div>
                                            @empty
                                                <div class="text-gray-500 italic">No file changes found in this commit.
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-center py-10">
                                <div class="text-gray-500 mb-2">No history loaded.</div>
                                <button wire:click="showHistory"
                                    class="text-indigo-400 hover:text-indigo-300 text-sm">Refresh History</button>
                            </div>
                        @endforelse
                    </div>
                @elseif($viewMode === 'diff')
                    <!-- Diff Viewer -->
                    <div class="absolute inset-0 flex flex-col bg-gray-900 z-10">
                        <div
                            class="bg-gray-800 px-4 py-2 flex justify-between items-center border-b border-gray-700 shadow-md">
                            <div class="flex items-center space-x-3">
                                <button wire:click="closeDiff"
                                    class="text-gray-400 hover:text-white transition-colors bg-gray-700 hover:bg-gray-600 rounded px-2 py-1 flex items-center text-xs">
                                    <span>&larr; Back</span>
                                </button>
                                <span class="text-sm font-medium text-gray-200 truncate max-w-[300px]"
                                    title="{{ $selectedFile }}">
                                    {{ $selectedFile }}
                                </span>
                            </div>
                            <span
                                class="text-xs text-gray-500 font-mono border border-gray-700 rounded px-2 py-0.5 bg-gray-800">{{ substr($selectedCommitHash, 0, 7) }}</span>
                        </div>
                        <div class="flex-1 overflow-auto p-4 font-mono text-xs text-gray-300 bg-gray-950">
                            @if (empty($fileDiff))
                                <div class="text-center text-gray-500 mt-10 italic">Binary file or no content changes.
                                </div>
                            @else
                                <pre class="whitespace-pre-wrap leading-relaxed">
@foreach (explode("\n", $fileDiff) as $line)
@php
    $class = 'text-gray-300';
    if (str_starts_with($line, '+')) {
        $class = 'text-green-400 bg-green-900/10 block border-l-2 border-green-500/50 pl-2';
    } elseif (str_starts_with($line, '-')) {
        $class = 'text-red-400 bg-red-900/10 block border-l-2 border-red-500/50 pl-2';
    } elseif (str_starts_with($line, '@@')) {
        $class = 'text-purple-400 block mt-4 mb-2 font-bold bg-gray-900 p-1 border border-gray-800 rounded';
    } else {
        $class = 'pl-2.5 block text-gray-400';
    }
@endphp
<span class="{{ $class }}">{{ $line }}</span>
@endforeach
</pre>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
