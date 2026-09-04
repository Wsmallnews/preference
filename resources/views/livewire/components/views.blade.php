@php
    use Filament\Support\Icons\Heroicon;

    // 调用方传入的路由名（hrefRoute）→ 行项跳转链接闭包
    $href = $hrefRoute ? fn ($record) => sn_route($hrefRoute, $record) : null;

    $heading = match ($listType) {
        'preferencer' => $this->getProperty('heading', __('sn-preference::preference.components.views_title_user')),
        'preferenceable' => $this->getProperty('heading', __('sn-preference::preference.components.views_title_subject')),
        default => $this->getProperty('heading', __('sn-preference::preference.components.views_title')),
    };

    $selectAllLabel = count($selected) === $views->count() && $views->count() > 0
        ? __('sn-preference::preference.action.deselect_all')
        : __('sn-preference::preference.action.select_all');
@endphp

<div class="sn-preference-list w-full @container">
    <div @class(['w-full sn-container' => $contained])>
        {{-- Header --}}
        @if ($this->isManageable() && $manageMode)
            <div class="sn-list-header justify-between flex-wrap gap-x-3 gap-y-1 bg-gray-50/50 dark:bg-gray-800/50">
                <button wire:click="toggleSelectAll"
                    class="sn-tip-text flex items-center gap-1 hover:text-gray-700 dark:hover:text-gray-300 sn-transition-colors">
                    <x-filament::icon icon="heroicon-m-list-bullet" class="size-4" />
                    {{ $selectAllLabel }}
                </button>
                <h3 class="sn-h3-text">{{ $heading }}</h3>
                <button wire:click="toggleManageMode"
                    class="sn-btn sn-btn-sm sn-btn-ghost-primary">
                    {{ __('sn-preference::preference.action.done') }}
                </button>
            </div>
        @else
            <div class="sn-list-header justify-between flex-wrap gap-x-3 gap-y-1">
                <h3 class="sn-h3-text">{{ $heading }}</h3>
                <div class="flex items-center gap-3">
                    <span class="sn-tip-text flex items-center gap-1">
                        <x-filament::icon icon="heroicon-m-eye" class="size-4" />
                        <span>{{ $this->getCount() }}</span>
                    </span>
                    @if ($this->isManageable())
                        <button wire:click="toggleManageMode"
                            class="sn-btn sn-btn-sm sn-btn-ghost-primary">
                            {{ __('sn-preference::preference.action.manage') }}
                        </button>
                    @endif
                </div>
            </div>
        @endif

        @if ($views->isNotEmpty())
            <x-sn-support::paginators.container
                class="w-full"
                :page-type="$pageType"
                :page-info="$pageInfo"
                :paginator-link="$paginatorLink"
                :page-name="$pageName"
            >
                @foreach ($views as $preference)
                    <div @class([
                        'sn-hover sn-link sn-list-row flex items-center gap-3 sn-transition-colors',
                        'sn-active' => $manageMode && $this->isSelected($preference->id),
                    ])
                        @if ($manageMode)
                            wire:click="toggleItem({{ $preference->id }})"
                        @endif
                    >
                        @if ($manageMode)
                            <input type="checkbox"
                                wire:click="toggleItem({{ $preference->id }})"
                                @checked($this->isSelected($preference->id))
                                class="w-5 h-5 rounded border-gray-300 text-primary-600 accent-primary-600 focus:ring-primary-500 shrink-0 cursor-pointer">
                        @endif

                        <div class="flex-1 min-w-0">
                            @if ($listType == 'preferenceable')
                                <x-sn-preference::preferencer
                                    :preference="$preference"
                                    :preferencer="$preference->preferencer"
                                    :has-link="$manageMode ? false : true"
                                    :href="$href"
                                    :embedded="true"
                                />
                            @elseif ($listType == 'preferencer')
                                <x-sn-preference::preferenceable
                                    :preference="$preference"
                                    :preferenceable="$preference->preferenceable"
                                    :has-link="$manageMode ? false : true"
                                    :href="$href"
                                    :embedded="true"
                                />
                            @else
                                <x-sn-preference::preference
                                    :preference="$preference"
                                    :has-link="$manageMode ? false : true"
                                    :href="$href"
                                    :embedded="true"
                                />
                            @endif
                        </div>

                        @if ($manageMode && $this->isManageable())
                            <div class="shrink-0" @click.stop>
                                {{ ($this->deleteViewAction)(['key' => $preference->id]) }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </x-sn-support::paginators.container>

            {{-- Batch action bar --}}
            @if ($manageMode)
                <div class="sn-list-footer sticky bottom-0 justify-between rounded-b-(--sn-radius-card)">
                    <button wire:click="toggleSelectAll"
                        class="sn-tip-text flex items-center gap-1 hover:text-gray-700 dark:hover:text-gray-300 sn-transition-colors">
                        <x-filament::icon icon="heroicon-m-list-bullet" class="size-4" />
                        {{ $selectAllLabel }}
                    </button>
                    <span class="sn-tip-text">
                        {{ __('sn-preference::preference.action.selected_count', ['count' => $this->getSelectedCount()]) }}
                    </span>
                    {{ ($this->batchDeleteAction) }}
                </div>
            @endif
        @else
            <x-sn-support::empty-state
                :contained="false"
                icon="heroicon-m-eye"
                icon-color="gray"
                :heading="$this->getEmptyLabel()"
                :description="$this->getEmptyTipLabel()"
            />
        @endif
    </div>

    <x-filament-actions::modals />
</div>
