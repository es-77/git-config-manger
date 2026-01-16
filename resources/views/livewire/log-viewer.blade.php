<div class="flex flex-col h-full bg-slate-900 text-white overflow-hidden">
    <!-- Header -->
    <div class="flex-none flex items-center justify-between px-6 py-4 border-b border-slate-700 bg-slate-800 shrink-0">
        <h1 class="text-xl font-semibold">System Logs</h1>
        <div class="flex items-center gap-4">
            <!-- Tabs -->
            <div class="flex space-x-2 bg-slate-900 p-1 rounded-lg">
                <button wire:click="$set('activeTab', 'laravel')" 
                        class="px-4 py-2 rounded-md text-sm font-medium transition-colors {{ $activeTab === 'laravel' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800' }}">
                    Application Logs
                </button>
                <button wire:click="$set('activeTab', 'commands')" 
                        class="px-4 py-2 rounded-md text-sm font-medium transition-colors {{ $activeTab === 'commands' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800' }}">
                    Command History
                </button>
            </div>

            <div class="w-px h-6 bg-slate-700 mx-2"></div>

            <!-- Actions -->
            <button wire:click="refreshLogs" 
                    class="p-2 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg transition-colors"
                    title="Refresh">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </button>
            
            <button wire:click="clearLogs" 
                    wire:confirm="Are you sure you want to clear these logs?"
                    class="p-2 text-red-400 hover:text-red-300 hover:bg-red-400/10 rounded-lg transition-colors"
                    title="Clear Logs">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Content Area (Scrollable) -->
    <div class="flex-1 overflow-hidden relative">
        <div class="absolute inset-0 overflow-y-auto p-4 font-mono text-sm scroll-smooth">
            @if($activeTab === 'laravel')
                <div class="space-y-1">
                    @if(empty($laravelLogs))
                        <div class="flex flex-col items-center justify-center h-64 text-slate-500">
                            <svg class="w-12 h-12 mb-2 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p>No application logs found.</p>
                        </div>
                    @else
                        @foreach($laravelLogs as $log)
                            <div class="p-3 rounded-md bg-slate-800 border-l-4 {{ str_contains($log, '.ERROR') ? 'border-red-500 bg-red-500/5' : (str_contains($log, '.WARNING') ? 'border-yellow-500 bg-yellow-500/5' : 'border-blue-500 bg-blue-500/5') }} transition-colors hover:bg-slate-700/50">
                                <pre class="whitespace-pre-wrap break-words text-slate-300 font-code text-xs leading-relaxed">{{ $log }}</pre>
                            </div>
                        @endforeach
                    @endif
                </div>
            @else
                <div class="space-y-2">
                    @if(empty($commandLogs))
                        <div class="flex flex-col items-center justify-center h-64 text-slate-500">
                            <svg class="w-12 h-12 mb-2 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p>No command history found.</p>
                        </div>
                    @else
                        @foreach($commandLogs as $log)
                            <div class="group p-4 rounded-lg bg-slate-800 border-l-4 {{ $log['success'] ? 'border-emerald-500' : 'border-red-500' }} hover:shadow-lg transition-all duration-200">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-1 rounded text-xs font-bold uppercase tracking-wider {{ $log['success'] ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400' }}">
                                            {{ $log['success'] ? 'SUCCESS' : 'FAILED' }}
                                        </span>
                                        <span class="font-mono font-semibold text-slate-200 text-sm select-all">$ {{ $log['command'] }}</span>
                                    </div>
                                    <span class="text-xs text-slate-500 font-mono">{{ $log['timestamp'] }}</span>
                                </div>
                                
                                @if($log['output'])
                                    <div class="mt-3 bg-slate-900 rounded p-3 overflow-x-auto">
                                        <div class="text-[10px] uppercase font-bold text-slate-500 mb-1 tracking-wider">Output</div>
                                        <pre class="font-mono text-xs text-slate-300 whitespace-pre-wrap">{{ $log['output'] }}</pre>
                                    </div>
                                @endif
                                
                                @if($log['error'])
                                    @php
                                        $isSuccess = $log['success'];
                                        $borderColor = $isSuccess ? 'border-slate-500/20' : 'border-red-500/20';
                                        $bgColor = $isSuccess ? 'bg-slate-900' : 'bg-red-900/10';
                                        $textColor = $isSuccess ? 'text-slate-400' : 'text-red-400';
                                        $contentColor = $isSuccess ? 'text-slate-300' : 'text-red-300';
                                        $labelText = $isSuccess ? 'Command Info / Progress' : 'Error Output';
                                    @endphp
                                    <div class="mt-3 {{ $bgColor }} rounded p-3 overflow-x-auto border {{ $borderColor }}">
                                        <div class="text-[10px] uppercase font-bold {{ $textColor }} mb-1 tracking-wider">{{ $labelText }}</div>
                                        <pre class="font-mono text-xs {{ $contentColor }} whitespace-pre-wrap">{{ $log['error'] }}</pre>
                                    </div>
                                @endif
                                
                                @if($log['path'])
                                    <div class="mt-2 flex items-center gap-1 text-xs text-slate-600">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                        </svg>
                                        <span class="font-mono">{{ $log['path'] }}</span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
