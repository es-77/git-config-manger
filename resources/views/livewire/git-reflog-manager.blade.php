<div class="h-full flex flex-col">
    <!-- Header -->
    <div class="bg-gray-800 border-b border-gray-700 p-4 flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <h1 class="text-xl font-semibold text-white">Git Reflog</h1>
            @if($repositoryPath)
                <span class="text-gray-400 text-sm bg-gray-700 px-2 py-1 rounded">
                    {{ basename($repositoryPath) }}
                </span>
                @if($currentHead)
                    <div class="flex items-center text-xs space-x-2 bg-indigo-900/30 px-3 py-1.5 rounded-full border border-indigo-500/30">
                        <span class="text-indigo-400 font-bold">HEAD:</span>
                        <span class="font-mono text-gray-300">{{ $currentHead['hash'] }}</span>
                        <span class="text-gray-400 truncate max-w-[200px]">{{ $currentHead['message'] }}</span>
                        <span class="text-gray-500">({{ $currentHead['date'] }})</span>
                    </div>
                @endif
            @endif
        </div>
        <div>
            @if($repositoryPath)
                <div class="flex items-center space-x-2">
                     <button wire:click="backToRepos" 
                        class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition-colors flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        <span>Back</span>
                    </button>
                    <button wire:click="selectRepository" 
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                        </svg>
                        <span>Change Repo</span>
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Content -->
    <div class="flex-1 overflow-auto p-6">
        @if(!$repositoryPath)
            <div class="max-w-4xl mx-auto">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-medium text-white">Select a Repository</h2>
                    <button wire:click="selectRepository" 
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors flex items-center space-x-2 shadow-lg shadow-indigo-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Open Repository</span>
                    </button>
                </div>

                @if(count($recentRepos) > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($recentRepos as $repo)
                            <div wire:click="openRecent('{{ $repo['path'] }}')" 
                                class="bg-gray-800 p-4 rounded-xl border border-gray-700 hover:border-indigo-500 hover:bg-gray-750 transition-all cursor-pointer group shadow-sm hover:shadow-indigo-500/10 flex flex-col">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex items-center space-x-3">
                                        <div class="p-2 bg-gray-700 rounded-lg group-hover:bg-indigo-500/20 group-hover:text-indigo-400 transition-colors">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-white group-hover:text-indigo-400 transition-colors">{{ $repo['name'] }}</h3>
                                            <p class="text-xs text-gray-500 truncate max-w-[250px]" title="{{ $repo['path'] }}">{{ $repo['path'] }}</p>
                                        </div>
                                    </div>
                                    <button wire:click.stop="removeRepo('{{ $repo['path'] }}')" 
                                        class="p-1 text-gray-500 hover:text-red-400 hover:bg-gray-700 rounded transition-colors"
                                        title="Remove from history">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                                <div class="mt-auto pt-2 flex items-center justify-between text-xs text-gray-500">
                                    <span>Last opened: {{ \Carbon\Carbon::parse($repo['last_accessed'])->diffForHumans() }}</span>
                                    <span class="group-hover:translate-x-1 transition-transform text-indigo-500 font-medium">Open &rarr;</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12 border-2 border-dashed border-gray-700 rounded-xl bg-gray-800/50">
                        <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-400">No recent repositories</h3>
                        <p class="text-gray-500 mt-1">Open a repository to start tracking reflog history</p>
                    </div>
                @endif
            </div>
        @else
            <div class="bg-gray-800 rounded-lg shadow border border-gray-700 overflow-hidden h-full flex flex-col">
                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-left text-sm text-gray-400 relative">
                        <thead class="bg-gray-700 text-gray-200 uppercase font-medium sticky top-0 z-10">
                            <tr>
                                <th class="px-6 py-3">Hash</th>
                                <th class="px-6 py-3">Ref</th>
                                <th class="px-6 py-3">Action</th>
                                <th class="px-6 py-3">Message</th>
                                <th class="px-6 py-3">Date</th>
                                <th class="px-6 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700 overflow-y-auto">
                            @forelse($reflog as $entry)
                                <tr class="hover:bg-gray-700/50 transition-colors group">
                                    <td class="px-6 py-4 font-mono text-indigo-400">
                                        {{ $entry['hash'] }}
                                    </td>
                                    <td class="px-6 py-4 font-mono text-yellow-500 text-xs">
                                        {{ $entry['selector'] }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $action = strtolower($entry['action']);
                                            $color = match(true) {
                                                str_contains($action, 'commit') => 'bg-green-900/50 text-green-300 border-green-700',
                                                str_contains($action, 'checkout') => 'bg-blue-900/50 text-blue-300 border-blue-700',
                                                str_contains($action, 'reset') => 'bg-red-900/50 text-red-300 border-red-700',
                                                str_contains($action, 'rebase') => 'bg-purple-900/50 text-purple-300 border-purple-700',
                                                str_contains($action, 'pull') || str_contains($action, 'fetch') => 'bg-cyan-900/50 text-cyan-300 border-cyan-700',
                                                str_contains($action, 'merge') => 'bg-pink-900/50 text-pink-300 border-pink-700',
                                                default => 'bg-gray-700 text-gray-300 border-gray-600'
                                            };
                                        @endphp
                                        <span class="px-2 py-1 rounded text-xs font-semibold border {{ $color }}">
                                            {{ $entry['action'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-white text-sm">
                                        {{ $entry['message'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                        {{ $entry['date'] }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button wire:click="checkout('{{ $entry['selector'] }}')"
                                                title="Checkout this state (Detached HEAD)"
                                                class="p-1.5 text-blue-400 hover:text-white bg-blue-400/10 hover:bg-blue-500 rounded transition-colors">
                                                Checkout
                                            </button>
                                            
                                            <div x-data="{ open: false }" class="relative">
                                                <button @click="open = !open" @click.away="open = false" 
                                                    class="p-1.5 text-red-400 hover:text-white bg-red-400/10 hover:bg-red-500 rounded transition-colors flex items-center">
                                                    <span>Reset</span>
                                                    <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                    </svg>
                                                </button>
                                                
                                                <!-- Dropdown -->
                                                <div x-show="open" 
                                                    class="absolute right-0 mt-2 w-48 bg-gray-800 rounded-md shadow-lg py-1 z-50 border border-gray-700"
                                                    style="display: none;">
                                                    <button wire:click="resetRef('{{ $entry['selector'] }}', 'soft'); open = false;"
                                                        class="block w-full text-left px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white">
                                                        Soft (Keep changes)
                                                    </button>
                                                    <button wire:click="resetRef('{{ $entry['selector'] }}', 'mixed'); open = false;"
                                                        class="block w-full text-left px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white">
                                                        Mixed (Unstage changes)
                                                    </button>
                                                    <button wire:click="resetRef('{{ $entry['selector'] }}', 'hard'); open = false;"
                                                        class="block w-full text-left px-4 py-2 text-sm text-red-400 hover:bg-red-900/50 hover:text-red-300">
                                                        Hard (Discard changes)
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        No reflog entries found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
