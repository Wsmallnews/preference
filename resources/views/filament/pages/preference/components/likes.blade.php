@php
    use Filament\Support\Icons\Heroicon;
@endphp

<div class="w-full">
    <div @class([
        'sn-container px-4 py-8' => $contained,
        'w-full flex flex-col gap-4',
    ])>
        <div class="flex items-center justify-between">
            <h3 class="n-h3-text">
                @if ($preferencer)
                    {{ __('sn-preference::preference.components.likes_title_user') }}
                @else
                    {{ __('sn-preference::preference.components.likes_title_subject') }}
                @endif
            </h3>
        </div>

        @if ($likes->isNotEmpty())
            <x-sn-support::paginators.container
                class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4"
                :page-type="$pageType"
                :page-info="$pageInfo"
                :paginator-link="$paginatorLink"
                :page-name="$pageName"
            >
                @foreach($likes as $preference)
                    @php
                        $item = $widgetType === 'preferencer' ? $preference->preferenceable : $preference->preferencer;
                    @endphp
                    @if ($item)
                        <div
                            class="group flex flex-col items-center gap-3 p-4 rounded-xl
                                   bg-white border border-pink-100
                                   hover:border-pink-300 hover:shadow-md hover:shadow-pink-100/50
                                   cursor-pointer transition-all duration-200"
                        >
                            <div class="relative">
                                @if ($item->avatar_url ?? false)
                                    <x-filament::avatar
                                        :src="files_url($item->avatar_url)"
                                        :alt="$item->name ?? ''"
                                        size="lg"
                                    />
                                @else
                                    <div class="size-12 rounded-full bg-gradient-to-br from-pink-100 to-rose-100 flex items-center justify-center
                                                group-hover:from-pink-200 group-hover:to-rose-200 transition-all duration-200">
                                        <x-filament::icon icon="heroicon-m-heart" class="size-6 text-rose-500" />
                                    </div>
                                @endif

                                @if ($widgetType === 'preferenceable')
                                    <div class="absolute -bottom-1 -right-1 size-5 rounded-full bg-rose-500 flex items-center justify-center
                                                shadow-sm shadow-rose-200">
                                        <x-filament::icon icon="heroicon-m-heart" class="size-3 text-white" />
                                    </div>
                                @endif
                            </div>
                            <div class="text-center min-w-0 w-full">
                                <p class="text-sm font-medium text-rose-900 truncate" title="{{ $item->name ?? $item->title ?? '' }}">
                                    {{ \Illuminate\Support\Str::limit($item->name ?? $item->title ?? 'Untitled', 16) }}
                                </p>
                                <p class="text-xs text-pink-400 mt-1">
                                    {{ $preference->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    @endif
                @endforeach
            </x-sn-support::paginators.container>
        @else
            <x-filament::empty-state
                :contained="false"
                icon="heroicon-m-heart"
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