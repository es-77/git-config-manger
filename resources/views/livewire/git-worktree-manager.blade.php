<div class="h-full flex flex-col p-6 space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center bg-gray-800 p-4 rounded-xl border border-gray-700">
        <div>
            <h1 class="text-2xl font-bold text-white">Git Worktree Manager</h1>
            <p class="text-gray-400 text-sm">Manage multiple worktrees for parallel branch development</p>
        </div>
        <div>
            @if($repositoryPath)
                <button wire:click="backToRepos" 
                    class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition-colors flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>Back to Repos</span>
                </button>
            @endif
        </div>
    </div>

    @if(!$repositoryPath)
         <!-- Recent Repositories View -->
        <div class="max-w-4xl mx-auto w-full">
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
                                <span class="group-hover:translate-x-1 transition-transform text-indigo-500 font-medium">Manage Worktrees &rarr;</span>
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
                    <p class="text-gray-500 mt-1">Open a repository to manage worktrees</p>
                </div>
            @endif
        </div>
    @else
        <!-- Section 1: Repository Info -->
        <div class="bg-gray-800 rounded-xl p-4 border border-gray-700 shadow-sm">
            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Selected Repository</label>
            <div class="flex space-x-2 items-center">
                <div class="flex-1 bg-gray-900 border border-gray-600 rounded-lg px-4 py-2 text-gray-300 flex items-center truncate">
                    @if($repositoryPath)
                        <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="truncate" title="{{ $repositoryPath }}">{{ $repositoryPath }}</span>
                    @endif
                </div>
                <button wire:click="selectRepository" 
                    class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                    </svg>
                    <span>Change Repo</span>
                </button>
            </div>
        </div>

        <!-- Section 2: Create New Worktree -->
        <div class="bg-gray-800 rounded-xl p-4 border border-gray-700 shadow-sm">
            <h2 class="text-sm font-semibold text-gray-300 uppercase tracking-wider mb-3">Create New Worktree</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Branch Name -->
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1">Branch Name</label>
                    <input wire:model.live="newBranch" type="text" placeholder="feature-branch"
                        class="w-full bg-gray-900 border border-gray-600 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                    @error('newBranch') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Create New Branch Toggle -->
                <div class="flex items-center">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input wire:model="createNewBranch" type="checkbox" 
                            class="w-4 h-4 text-indigo-600 bg-gray-900 border-gray-600 rounded focus:ring-indigo-500">
                        <span class="text-sm text-gray-300">Create new branch</span>
                    </label>
                </div>

                <!-- Target Path -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-400 mb-1">Target Directory Path</label>
                    <div class="flex space-x-2">
                        <input wire:model="newPath" type="text" placeholder="/path/to/worktree"
                            class="flex-1 bg-gray-900 border border-gray-600 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500 font-mono">
                        <button wire:click="pickPath"
                            class="bg-gray-700 hover:bg-gray-600 text-white px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                            Browse...
                        </button>
                    </div>
                    @error('newPath') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Create Button -->
                <div class="md:col-span-2">
                    <button wire:click="createWorktree" 
                        {{ !$newBranch || !$newPath ? 'disabled' : '' }}
                        class="w-full bg-green-600 hover:bg-green-500 disabled:bg-gray-600 disabled:cursor-not-allowed text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Create Worktree</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Section 3: Worktree List -->
        <div class="flex-1 overflow-y-auto">
            <h2 class="text-sm font-semibold text-gray-300 uppercase tracking-wider mb-3">Existing Worktrees</h2>
            
            @if($loading)
                <div class="flex items-center justify-center h-32">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-500"></div>
                </div>
            @elseif(count($worktrees) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($worktrees as $worktree)
                        <div class="bg-gray-800 hover:bg-gray-750 border border-gray-700 rounded-xl p-4 transition-all group">
                            <!-- Branch Name -->
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                            d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01">
                                        </path>
                                    </svg>
                                    <span class="font-semibold text-white">{{ $worktree['branch'] ?? 'Unknown' }}</span>
                                </div>
                                
                                <!-- Status Badge -->
                                @if(isset($worktree['isMain']) && $worktree['isMain'])
                                    <span class="px-2 py-1 bg-blue-600 text-white text-xs rounded-full font-medium">Main</span>
                                @elseif(isset($worktree['clean']) && $worktree['clean'])
                                    <span class="px-2 py-1 bg-green-600 text-white text-xs rounded-full font-medium">Clean</span>
                                @else
                                    <span class="px-2 py-1 bg-yellow-600 text-white text-xs rounded-full font-medium">Dirty</span>
                                @endif
                            </div>

                            <!-- Path -->
                            <div class="mb-3">
                                <p class="text-xs text-gray-400 mb-1">Path:</p>
                                <p class="text-sm text-gray-300 font-mono truncate" title="{{ $worktree['path'] }}">
                                    {{ $worktree['path'] }}
                                </p>
                            </div>

                            <!-- Actions -->
                            <div class="flex space-x-2">
                                <button wire:click="openWorktree('{{ $worktree['path'] }}')"
                                    class="flex-1 bg-gray-700 hover:bg-indigo-600 text-white px-3 py-1.5 rounded text-xs font-medium transition-colors flex items-center justify-center space-x-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                    </svg>
                                    <span>Open Folder</span>
                                </button>
                                
                                @if(!isset($worktree['isMain']) || !$worktree['isMain'])
                                    <button wire:click="removeWorktree('{{ $worktree['path'] }}')"
                                        wire:confirm="Are you sure you want to remove this worktree?"
                                        class="bg-gray-700 hover:bg-red-600 text-white px-3 py-1.5 rounded text-xs font-medium transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                @else
                                    <button disabled
                                        class="bg-gray-600 cursor-not-allowed text-gray-400 px-3 py-1.5 rounded text-xs font-medium">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center h-48 text-gray-500 bg-gray-800 rounded-xl border border-gray-700">
                    <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01">
                        </path>
                    </svg>
                    <p>No worktrees found. Create one above.</p>
                </div>
            @endif
        </div>
    @endif
</div>
