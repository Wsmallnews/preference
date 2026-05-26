@php
    use Filament\Support\Icons\Heroicon;
@endphp

<div class="w-full">
    <div @class([
        'w-full flex flex-col gap-4',
        'sn-container px-4 py-8' => $contained,
    ])>
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <h3 class="sn-h3-text">
                @if ($listType == 'preferencer')
                    {{ $this->getProperty('heading', __('sn-preference::preference.components.follows_title_user')) }}
                @elseif ($listType == 'preferenceable')
                    {{ $this->getProperty('heading', __('sn-preference::preference.components.follows_title_subject')) }}
                @else
                    {{ $this->getProperty('heading', __('sn-preference::preference.components.follows_title')) }}
                @endif
            </h3>
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-400 flex items-center gap-1">
                    <x-filament::icon icon="heroicon-m-user-group" class="size-4" />
                    <span>{{ $this->getCount() }}</span>
                </span>
                @if ($this->canManage())
                    @if ($manageMode)
                        <button
                            wire:click="toggleManageMode"
                            class="text-sm text-primary-600 hover:text-primary-700 font-medium"
                        >
                            {{ __('sn-preference::preference.action.done') }}
                        </button>
                    @else
                        <button
                            wire:click="toggleManageMode"
                            class="text-sm text-primary-600 hover:text-primary-700 font-medium"
                        >
                            {{ __('sn-preference::preference.action.manage') }}
                        </button>
                    @endif
                @endif
            </div>
        </div>

        @if ($follows->isNotEmpty())
            <x-sn-support::paginators.container
                class="w-full flex flex-col gap-2"
                :page-type="$pageType"
                :page-info="$pageInfo"
                :paginator-link="$paginatorLink"
                :page-name="$pageName"
            >
                @foreach ($follows as $preference)
                    <div @class([
                        'flex items-center gap-3 px-4 py-3 rounded-xl transition-colors',
                        'bg-primary-50/40 ring-1 ring-primary-200' => $manageMode && $this->isSelected($preference->id),
                        'bg-white hover:bg-gray-50' => ! $manageMode || ! $this->isSelected($preference->id),
                    ])>
                        @if ($manageMode)
                            <input
                                type="checkbox"
                                wire:click="toggleItem({{ $preference->id }})"
                                @checked($this->isSelected($preference->id))
                                class="w-5 h-5 rounded border-gray-300 text-primary-600 focus:ring-primary-500 shrink-0 cursor-pointer"
                            >
                        @endif

                        <div class="flex-1 min-w-0 cursor-pointer" @if(!$manageMode) wire:click="toggleItem({{ $preference->id }})" @endif>
                            @if ($listType == 'preferenceable')
                                <x-sn-preference::preferencer
                                    :preference="$preference"
                                    :preferencer="$preference->preferencer"
                                    contained
                                    :isLink="!$manageMode"
                                >
                                    @if ($preference->options['followed_at'] ?? false)
                                        <x-slot name="badge">
                                            <span class="inline-flex items-center gap-1 shrink-0 text-[10px] font-semibold px-1.5 py-px rounded bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300">
                                                {{ __('sn-preference::preference.widget.mutual_follow') }}
                                            </span>
                                        </x-slot>

                                        <x-slot name="extra">
                                            {{ $preference->options['followed_at'] }} {{ __('sn-preference::preference.widget.mutual_follow') }}
                                        </x-slot>
                                    @endif
                                </x-sn-preference::preferencer>
                            @elseif ($listType == 'preferencer')
                                <x-sn-preference::preferenceable
                                    :preference="$preference"
                                    :preferenceable="$preference->preferenceable"
                                    contained
                                    :isLink="!$manageMode"
                                >
                                    @if ($preference->options['followed_at'] ?? false)
                                        <x-slot name="badge">
                                            <span class="inline-flex items-center gap-1 shrink-0 text-[10px] font-semibold px-1.5 py-px rounded bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300">
                                                {{ __('sn-preference::preference.widget.mutual_follow') }}
                                            </span>
                                        </x-slot>

                                        <x-slot name="extra">
                                            {{ $preference->options['followed_at'] }} {{ __('sn-preference::preference.widget.mutual_follow') }}
                                        </x-slot>
                                    @endif
                                </x-sn-preference::preferenceable>
                            @else
                                <x-sn-preference::preference
                                    :preference="$preference"
                                    contained
                                    :isLink="!$manageMode"
                                >
                                    @if ($preference->options['followed_at'] ?? false)
                                        <x-slot name="badge">
                                            <span class="inline-flex items-center gap-1 shrink-0 text-[10px] font-semibold px-1.5 py-px rounded bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300">
                                                {{ __('sn-preference::preference.widget.mutual_follow') }}
                                            </span>
                                        </x-slot>

                                        <x-slot name="extra">
                                            {{ $preference->options['followed_at'] }} {{ __('sn-preference::preference.widget.mutual_follow') }}
                                        </x-slot>
                                    @endif
                                </x-sn-preference::preference>
                            @endif
                        </div>

                        @if (! $manageMode && $this->canManage())
                            <div class="shrink-0">
                                {{ ($this->unfollowAction)(['key' => $preference->id]) }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </x-sn-support::paginators.container>

            {{-- Batch action bar --}}
            @if ($manageMode)
                <div class="sticky bottom-0 flex items-center justify-between px-4 py-3 bg-white border-t border-gray-200 rounded-b-xl shadow-[0_-2px_8px_rgba(0,0,0,0.04)] -mx-4 -mb-8">
                    <button
                        wire:click="toggleSelectAll"
                        class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1"
                    >
                        <x-filament::icon icon="heroicon-m-list-bullet" class="size-4" />
                        @if (count($selected) === $follows->count() && $follows->count() > 0)
                            {{ __('sn-preference::preference.action.deselect_all') }}
                        @else
                            {{ __('sn-preference::preference.action.select_all') }}
                        @endif
                    </button>

                    <span class="text-sm text-gray-500">
                        {{ __('sn-preference::preference.action.selected_count', ['count' => $this->getSelectedCount()]) }}
                    </span>

                    {{ ($this->batchUnfollowAction) }}
                </div>
            @endif
        @else
            <x-filament::empty-state
                :contained="false"
                icon="heroicon-m-user-group"
                icon-color="gray"
            >
                <x-slot name="heading">
                    {{ $this->getEmptyLabel() }}
                </x-slot>

                <x-slot name="description">
                    {{ $this->getEmptyTipLabel() }}
                </x-slot>
            </x-filament::empty-state>
        @endif
    </div>

    <x-filament-actions::modals />
</div>
