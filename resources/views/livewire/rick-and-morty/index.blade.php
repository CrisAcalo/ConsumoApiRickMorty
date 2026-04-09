<div class="flex flex-col h-full w-full">
    <div class="flex items-center justify-between mb-4">
        <flux:heading size="xl">{{ __('Rick & Morty API') }}</flux:heading>
    </div>

    <div class="flex items-center gap-2 mb-6 border-b border-zinc-200 dark:border-zinc-700 pb-4">
        <flux:button wire:click="setTab('character')" variant="{{ $tab === 'character' ? 'primary' : 'ghost' }}"
            icon="users">{{ __('Characters') }}</flux:button>
        <flux:button wire:click="setTab('episode')" variant="{{ $tab === 'episode' ? 'primary' : 'ghost' }}"
            icon="film">{{ __('Episodes') }}</flux:button>
    </div>

    <div class="flex flex-1 flex-col pb-4 h-full" wire:loading.class="opacity-50">

        @if ($tab === 'character')
            <div wire:key="filters-character"
                class="mb-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-xl border border-zinc-200 dark:border-zinc-700">

                <flux:input x-on:input.debounce.500ms="$wire.set('characterName', $event.target.value)"
                    value="{{ $characterName }}" placeholder="{{ __('Name...') }}" icon="magnifying-glass" />

                <flux:select wire:model.live="characterStatus" placeholder="{{ __('All Statuses') }}">
                    <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
                    <flux:select.option value="alive">{{ __('Alive') }}</flux:select.option>
                    <flux:select.option value="dead">{{ __('Dead') }}</flux:select.option>
                    <flux:select.option value="unknown">{{ __('Unknown') }}</flux:select.option>
                </flux:select>
                <flux:input x-on:input.debounce.500ms="$wire.set('characterSpecies', $event.target.value)"
                    value="{{ $characterSpecies }}" placeholder="{{ __('Species...') }}" />
                <flux:input x-on:input.debounce.500ms="$wire.set('characterType', $event.target.value)"
                    value="{{ $characterType }}" placeholder="{{ __('Type...') }}" />
                <flux:select wire:model.live="characterGender" placeholder="{{ __('All Genders') }}">
                    <flux:select.option value="">{{ __('All Genders') }}</flux:select.option>
                    <flux:select.option value="female">{{ __('Female') }}</flux:select.option>
                    <flux:select.option value="male">{{ __('Male') }}</flux:select.option>
                    <flux:select.option value="genderless">{{ __('Genderless') }}</flux:select.option>
                    <flux:select.option value="unknown">{{ __('Unknown') }}</flux:select.option>
                </flux:select>
            </div>
        @elseif ($tab === 'episode')
            <div wire:key="filters-episode"
                class="mb-4 grid grid-cols-1 sm:grid-cols-2 gap-3 bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-xl border border-zinc-200 dark:border-zinc-700">
                <flux:input x-on:input.debounce.500ms="$wire.set('episodeName', $event.target.value)"
                    value="{{ $episodeName }}" placeholder="{{ __('Episode Name...') }}" icon="magnifying-glass" />
                <flux:input x-on:input.debounce.500ms="$wire.set('episodeCode', $event.target.value)"
                    value="{{ $episodeCode }}" placeholder="{{ __('Episode Code (e.g. S01E01)...') }}"
                    icon="hashtag" />
            </div>
        @endif

        @if ($this->apiData)
            @if ($tab === 'character')
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach ($this->apiData['results'] as $character)
                        <flux:card class="flex items-start gap-4" wire:key="char-{{ $character['id'] }}">
                            <flux:avatar src="{{ $character['image'] }}" class="w-16 h-16 shrink-0" />
                            <div class="flex flex-col min-w-0">
                                <flux:heading size="lg" class="truncate">{{ $character['name'] }}</flux:heading>
                                <div class="mt-1 flex flex-wrap items-center gap-2">
                                    <flux:badge size="sm"
                                        color="{{ $character['status'] === 'Alive' ? 'success' : ($character['status'] === 'Dead' ? 'danger' : 'warning') }}">
                                        {{ $character['status'] }}</flux:badge>
                                    <flux:text class="text-sm text-zinc-500 truncate">
                                        {{ $character['species'] }}{{ $character['gender'] !== 'unknown' ? ' - ' . $character['gender'] : '' }}
                                    </flux:text>
                                </div>
                                <div class="mt-3 overflow-hidden">
                                    <flux:text class="text-[10px] text-zinc-400 font-bold uppercase tracking-wider">
                                        {{ __('Last known location') }}</flux:text>
                                    <flux:text class="text-sm truncate" title="{{ $character['location']['name'] }}">
                                        {{ $character['location']['name'] }}</flux:text>
                                </div>
                            </div>
                        </flux:card>
                    @endforeach
                </div>
            @elseif ($tab === 'episode')
                <div
                    class="p-8 relative flex-1 rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden bg-white dark:bg-zinc-900">
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>{{ __('Episode') }}</flux:table.column>
                            <flux:table.column>{{ __('Name') }}</flux:table.column>
                            <flux:table.column>{{ __('Air Date') }}</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @foreach ($this->apiData['results'] as $episode)
                                <flux:table.row :key="$episode['id']">
                                    <flux:table.cell>
                                        <flux:badge color="zinc">{{ $episode['episode'] }}</flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell class="font-medium">
                                        {{ $episode['name'] }}
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        {{ $episode['air_date'] }}
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>
            @endif

            <!-- Pagination Controls -->
            <div class="mt-6 flex items-center justify-between pt-4 pb-2">
                <flux:text class="text-sm text-zinc-500">
                    {{ __('Showing page :page of :total', ['page' => $page, 'total' => collect($this->apiData['info'])->get('pages')]) }}
                </flux:text>
                <div class="flex gap-2">
                    <flux:button wire:click="previousPage" :disabled="$this->apiData['info']['prev'] === null"
                        size="sm" icon="chevron-left">{{ __('Previous') }}</flux:button>
                    <flux:button wire:click="nextPage" :disabled="$this->apiData['info']['next'] === null"
                        size="sm" icon-trailing="chevron-right">{{ __('Next') }}</flux:button>
                </div>
            </div>
        @else
            <div
                class="flex flex-col items-center justify-center p-12 text-zinc-500 border border-dashed border-zinc-300 dark:border-zinc-700 rounded-xl">
                <flux:icon name="magnifying-glass" class="w-12 h-12 mb-4 text-zinc-300 dark:text-zinc-600" />
                <flux:heading>{{ __('No results found.') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Try adjusting your filters.') }}</flux:text>
            </div>
        @endif
    </div>
</div>
</div>
