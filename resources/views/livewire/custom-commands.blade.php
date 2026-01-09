<div class="h-full flex flex-col p-6 space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-white">Custom Commands</h1>
            <p class="text-gray-400 text-sm">Create and organize your own Git workflows.</p>
        </div>
        <div>
            @if (!$isCreating && !$isEditing)
                <button wire:click="create"
                    class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>New Command</span>
                </button>
            @endif
        </div>
    </div>

    @if ($isCreating || $isEditing)
        <!-- Editor Form -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 shadow-lg max-w-3xl mx-auto w-full">
            <h2 class="text-lg font-bold text-white mb-4">{{ $isEditing ? 'Edit Command' : 'Create New Command' }}</h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Name</label>
                    <input wire:model="name" type="text" placeholder="e.g. Sync Branch"
                        class="w-full bg-gray-900 border border-gray-600 rounded-lg px-4 py-2 text-gray-200 focus:outline-none focus:border-indigo-500">
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Description</label>
                    <textarea wire:model="description" placeholder="What does this do?" rows="2"
                        class="w-full bg-gray-900 border border-gray-600 rounded-lg px-4 py-2 text-gray-200 focus:outline-none focus:border-indigo-500"></textarea>
                </div>

                <div class="flex items-center space-x-2">
                    <input type="checkbox" wire:model="askDirectory" id="askDirectory"
                        class="w-4 h-4 text-indigo-600 bg-gray-900 border-gray-600 rounded focus:ring-indigo-500 focus:ring-2">
                    <label for="askDirectory" class="text-sm text-gray-300 select-none cursor-pointer">Always ask for directory when running</label>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Steps</label>
                        <button wire:click="addStep" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium">+ Add Step</button>
                    </div>
                    
                    <div class="space-y-2">
                        @foreach($steps as $index => $step)
                            <div class="flex items-center space-x-2">
                                <span class="text-gray-500 font-mono text-sm w-6 text-right">{{ $index + 1 }}.</span>
                                <input wire:model="steps.{{ $index }}" type="text" placeholder="git command..."
                                    class="flex-1 bg-gray-900 border border-gray-600 rounded px-3 py-2 text-sm font-mono text-gray-300 focus:outline-none focus:border-indigo-500">
                                <button wire:click="removeStep({{ $index }})" 
                                    class="p-2 text-gray-500 hover:text-red-400 transition-colors" title="Remove step">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                            @error('steps.'.$index) <span class="text-red-500 text-xs ml-8">{{ $message }}</span> @enderror
                        @endforeach
                    </div>
                </div>

                <div class="pt-4 flex justify-end space-x-3 border-t border-gray-700 mt-4">
                    <button wire:click="cancel" class="px-4 py-2 rounded-lg text-gray-400 hover:text-white hover:bg-gray-700 transition-colors">Cancel</button>
                    <button wire:click="save" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg font-medium shadow-lg shadow-indigo-500/20 transition-colors">
                        Save Command
                    </button>
                </div>
            </div>
        </div>
    @else
        <!-- Command List -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 overflow-y-auto">
            @forelse ($commands as $cmd)
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-5 flex flex-col group hover:border-gray-600 transition-colors relative">
                     <!-- Loading Overlay for this card -->
                    <div wire:loading.flex wire:target="run('{{ $cmd['id'] }}')" 
                        class="absolute inset-0 bg-gray-900/80 backdrop-blur-sm z-10 flex-col items-center justify-center rounded-xl" style="display: none;">
                         <svg class="animate-spin w-8 h-8 text-indigo-500 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-sm font-medium text-gray-300">Running...</span>
                    </div>

                    <div class="flex justify-between items-start mb-2">
                        <h3 class="font-bold text-lg text-white truncate pr-2" title="{{ $cmd['name'] }}">{{ $cmd['name'] }}</h3>
                        <div class="flex space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button wire:click="edit('{{ $cmd['id'] }}')" class="p-1.5 text-gray-400 hover:text-white hover:bg-gray-700 rounded transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </button>
                            <button wire:click="delete('{{ $cmd['id'] }}')" 
                                wire:confirm="Are you sure you want to delete '{{ $cmd['name'] }}'?"
                                class="p-1.5 text-gray-400 hover:text-red-400 hover:bg-red-900/30 rounded transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>
                    
                    <p class="text-sm text-gray-400 mb-4 h-10 overflow-hidden text-ellipsis">{{ $cmd['description'] }}</p>

                    <div class="mt-auto space-y-2">
                        <div class="bg-gray-900 rounded p-2 text-xs font-mono text-gray-500 border border-gray-800">
                             @php $steps = $cmd['steps'] ?? [] @endphp
                             @foreach(array_slice($steps, 0, 2) as $step)
                                <div class="truncate">$ {{ $step }}</div>
                             @endforeach
                             @if(count($steps) > 2)
                                <div class="text-gray-600 italic">+ {{ count($steps) - 2 }} more steps</div>
                             @endif
                        </div>

                        <button wire:click="run('{{ $cmd['id'] }}')" 
                            class="w-full py-2 bg-gray-700 hover:bg-green-600 hover:text-white text-gray-200 rounded-lg font-medium transition-colors flex items-center justify-center space-x-2 group/run">
                            <svg class="w-4 h-4 text-green-500 group-hover/run:text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path></svg>
                            <span>Run Command</span>
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full flex flex-col items-center justify-center p-12 text-gray-500 border-2 border-dashed border-gray-700 rounded-xl">
                    <svg class="w-12 h-12 mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                    <p class="text-lg font-medium text-gray-400">No custom commands yet.</p>
                    <p class="text-sm mb-6">Create your first workflow to automate tasks.</p>
                    <button wire:click="create" class="text-indigo-400 hover:text-indigo-300 font-medium">Create Command &rarr;</button>
                </div>
            @endforelse
        </div>
        
        <!-- Repo Selection if missing -->
        @if(!$directory)
             <div class="fixed bottom-6 left-1/2 transform -translate-x-1/2 bg-yellow-900/90 text-yellow-100 px-6 py-3 rounded-full shadow-xl flex items-center space-x-3 backdrop-blur border border-yellow-700/50 z-50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span>No repository selected. Commands may fail.</span>
                <button wire:click="pickDirectory" class="bg-yellow-800 hover:bg-yellow-700 px-3 py-1 rounded text-sm font-bold transition-colors">Select Repo</button>
            </div>
        @endif

        <!-- Directory Picker Modal -->
        @if($showDirectoryModal)
            <div class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[60] flex items-center justify-center p-4">
                <div class="bg-gray-800 rounded-xl border border-gray-700 shadow-2xl w-full max-w-md overflow-hidden">
                    <div class="p-4 border-b border-gray-700 flex justify-between items-center">
                        <h3 class="font-bold text-white">Select Repository</h3>
                        <button wire:click="$set('showDirectoryModal', false)" class="text-gray-400 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <div class="p-2 space-y-1 max-h-60 overflow-y-auto">
                        @forelse($recentDirectories as $path)
                            <button wire:click="selectDirectory('{{ $path }}')" 
                                class="w-full text-left px-4 py-3 rounded hover:bg-gray-700 transition-colors group">
                                <div class="text-sm font-medium text-gray-200 group-hover:text-white truncate" title="{{ $path }}">{{ basename($path) }}</div>
                                <div class="text-xs text-gray-500 truncate" title="{{ $path }}">{{ $path }}</div>
                            </button>
                        @empty
                            <div class="text-center py-6 text-gray-500 text-sm">No recent directories.</div>
                        @endforelse
                    </div>
                    <div class="p-4 border-t border-gray-700 bg-gray-900/50">
                        <button wire:click="pickDirectory" class="w-full flex justify-center items-center space-x-2 bg-indigo-600 hover:bg-indigo-500 text-white py-2 rounded-lg font-medium transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                            <span>Browse for Folder...</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
