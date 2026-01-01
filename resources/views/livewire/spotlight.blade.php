<div class="min-h-screen flex flex-col items-center pt-4 bg-transparent" x-data
    @keydown.window.prevent.arrow-up="$wire.moveSelection('up')"
    @keydown.window.prevent.arrow-down="$wire.moveSelection('down')" @keydown.window.enter="$wire.handleEnter()"
    @keydown.window.escape="Native.window.close('spotlight')">
    <div class="w-full max-w-lg bg-gray-900 rounded-xl shadow-2xl border border-gray-700 overflow-hidden">
        <div class="p-4 border-b border-gray-700 flex items-center space-x-3">
            <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <input wire:model.live="search" type="text" placeholder="Search profiles..."
                class="bg-transparent border-none focus:ring-0 text-white w-full text-lg placeholder-gray-500"
                autofocus>
        </div>

        <div class="max-h-96 overflow-y-auto p-2">
            @forelse($filteredProfiles as $index => $profile)
                <div wire:click="selectProfile({{ $index }})"
                    class="p-3 rounded-lg flex items-center justify-between cursor-pointer transition-colors {{ $index === $activeIndex ? 'bg-indigo-600' : 'hover:bg-gray-800' }}">
                    <div class="flex items-center space-x-3">
                        <div
                            class="w-8 h-8 rounded-full bg-gray-700 flex items-center justify-center text-sm font-bold text-white {{ $index === $activeIndex ? 'bg-opacity-25' : '' }}">
                            {{ strtoupper(substr($profile['name'], 0, 1)) }}
                        </div>
                        <div>
                            <div class="font-bold text-white">{{ $profile['name'] }}</div>
                            <div class="text-xs {{ $index === $activeIndex ? 'text-indigo-200' : 'text-gray-400' }}">
                                {{ $profile['email'] }}</div>
                        </div>
                    </div>
                    @if ($index === $activeIndex)
                        <span class="text-xs text-indigo-200">Enter to select</span>
                    @endif
                </div>
            @empty
                <div class="p-4 text-center text-gray-500">
                    No profiles found matching "{{ $search }}"
                </div>
            @endforelse

            <div class="mt-2 pt-2 border-t border-gray-800 text-xs text-gray-500 px-2 flex justify-between">
                <span>Select a profile to apply git config locally</span>
                <span>Esc to close</span>
            </div>
        </div>
    </div>
</div>
