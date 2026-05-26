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
                @if ($listType == 'preferencer')
                    {{ __('sn-preference::preference.components.follows_title_user') }}
                @elseif ($listType == 'preferenceable')
                    {{ __('sn-preference::preference.components.follows_title_subject') }}
                @else
                    {{ __('sn-preference::preference.components.follows_title') }}
                @endif
            </h3>
            <div class="flex items-center gap-2 sn-tip-text">
                <x-filament::icon icon="heroicon-m-users" class="size-4" />
                <span>{{ $follows->count() }}</span>
            </div>
        </div>

        @if ($follows->isNotEmpty())
            <x-sn-support::paginators.container
                class="w-full flex flex-col gap-4"
                :page-type="$pageType"
                :page-info="$pageInfo"
                :paginator-link="$paginatorLink"
                :page-name="$pageName"
            >
                @foreach ($follows as $preference)
                    @if ($listType == 'preferenceable')
                        <x-sn-preference::preferencer
                            :preference="$preference"
                            :preferencer="$preference->preferencer"
                            contained
                            isLink
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
                            isLink
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
                            isLink
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
