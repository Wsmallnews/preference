@php
    use Filament\Support\Icons\Heroicon;
@endphp

<div class="w-full">
    <div @class([
        'sn-container px-4 py-8' => $contained,
        'w-full flex flex-col gap-4',
    ])>
        <div class="flex items-center justify-between">
            <h3 class="sn-h3-text">
                @if ($preferencer)
                    {{ __('sn-preference::preference.components.follows_title_user') }}
                @else
                    {{ __('sn-preference::preference.components.follows_title_subject') }}
                @endif
            </h3>
            <div class="flex items-center gap-2 text-sm text-rose-400">
                <x-filament::icon icon="heroicon-m-users" class="size-4" />
                <span>{{ $follows->count() }}</span>
            </div>
        </div>

        @if ($follows->isNotEmpty())
            <x-sn-support::paginators.container
                class="flex flex-col gap-3"
                :page-type="$pageType"
                :page-info="$pageInfo"
                :paginator-link="$paginatorLink"
                :page-name="$pageName"
            >
                @foreach($follows as $preference)
                    @php
                        $item = $widgetType === 'preferencer' ? $preference->preferenceable : $preference->preferencer;
                        $pivotOptions = $preference->options ?? null;
                        $isMutual = $pivotOptions && ($pivotOptions['followed_at'] ?? false);
                    @endphp
                    @if ($item)
                        <div
                            class="group flex items-center gap-4 p-4 rounded-xl
                                   bg-white border border-blue-50
                                   hover:border-blue-200 hover:shadow-sm hover:shadow-blue-100/50
                                   cursor-pointer transition-all duration-200"
                            x-data="{ showInfo: false }"
                            @mouseenter="showInfo = true"
                            @mouseleave="showInfo = false"
                        >
                            <div class="relative shrink-0">
                                @if ($item->avatar_url ?? false)
                                    <x-filament::avatar
                                        :src="files_url($item->avatar_url)"
                                        :alt="$item->name ?? ''"
                                        size="lg"
                                    />
                                @else
                                    <div class="size-12 rounded-full bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center
                                                group-hover:from-blue-200 group-hover:to-blue-300 transition-all duration-200">
                                        <x-filament::icon icon="heroicon-m-user" class="size-6 text-blue-500" />
                                    </div>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-rose-900 truncate" title="{{ $item->name ?? '' }}">
                                    {{ $item->name ?? 'Unknown' }}
                                </p>
                                <p class="text-xs text-blue-400 mt-0.5">
                                    {{ __('sn-preference::preference.widget.followed_at', ['time' => $preference->created_at->diffForHumans()]) }}
                                </p>
                            </div>

                            @if ($isMutual)
                                <div
                                    class="shrink-0 flex items-center gap-1 px-2.5 py-1 rounded-full
                                           bg-blue-50 text-blue-600 text-xs font-medium"
                                    x-show="showInfo"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 scale-90"
                                    x-transition:enter-end="opacity-100 scale-100"
                                >
                                    <x-filament::icon icon="heroicon-m-arrow-path-rounded-square" class="size-3" />
                                    <span>{{ __('sn-preference::preference.widget.mutual_follow') }}</span>
                                </div>
                            @endif

                            <div class="shrink-0 text-blue-300 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                <x-filament::icon icon="heroicon-m-chevron-right" class="size-5" />
                            </div>
                        </div>
                    @endif
                @endforeach
            </x-sn-support::paginators.container>
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
</div>