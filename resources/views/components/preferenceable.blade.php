@props([
    'preference',
    'preferenceable',
    'contained' => false,
    'isLink' => false,
])

@php
    use Filament\Support\Icons\Heroicon;
    use Wsmallnews\Support\Contracts\HasSnSubject;
    use Wsmallnews\Preference\Exceptions\PreferenceException;

    if (! $preferenceable instanceof HasSnSubject) {
        throw new PreferenceException(get_class($preferenceable) . ' model must implement `\Wsmallnews\Support\Contracts\HasSnSubject` interface.');
    }

    $timeLabel = match ($preference->type) {
        'follow' => __('sn-preference::preference.widget.followed_at', ['time' => $preference->created_at->diffForHumans()]),
        'like' => __('sn-preference::preference.widget.liked_at', ['time' => $preference->created_at->diffForHumans()]),
        default => __('sn-preference::preference.widget.viewed_at', ['time' => $preference->created_at->diffForHumans()]),
    };
@endphp

<div
    {{
        $attributes->class([
            'sn-container p-4' => $contained,
            'sn-hover sn-link' => $isLink,
            'flex items-center gap-4 justify-between group',
        ])
    }}
>
    <div class="w-12 h-12 shrink-0 overflow-hidden rounded-md bg-gray-100 dark:bg-gray-800">
        @if ($preferenceable->getSnPreferenceableCoverUrl())
            <img class="w-full h-full object-cover" src="{{ files_url($preferenceable->getSnPreferenceableCoverUrl()) }}" alt="{{ $preferenceable->getSnPreferenceableTitle() }}" />
        @else
            <div class="sn-image-placeholder rounded-md">
                <x-filament::icon :icon="Heroicon::Photo" class="size-5" aria-hidden="true" />
            </div>
        @endif
    </div>

    <div class="flex flex-col grow gap-1 min-w-0">
        <div class="flex items-center gap-2">
            @if ($preferenceable->getSnPreferenceableTitle())
                <span class="sn-content-text sn-truncate">
                    {{ $preferenceable->getSnPreferenceableTitle() }}
                </span>
            @endif

            @isset($badge)
                {{ $badge }}
            @endisset
        </div>

        @if ($preferenceable->getSnPreferenceableDescription())
            <span class="sn-descript-text sn-truncate-2">
                {{ $preferenceable->getSnPreferenceableDescription() }}
            </span>
        @endif

        @isset($extra)
            <div class="sn-tip-text">
                {{ $extra }}
            </div>
        @endisset
    </div>

    <div class="flex items-center gap-2">
        @isset($meta)
            {{ $meta }}
        @else
            <div class="sn-tip-text shrink-0">
                {{ $timeLabel }}
            </div>
            @if ($isLink)
                <div class="sn-gray-text shrink-0 hidden group-hover:block">
                    <x-filament::icon :icon="Heroicon::ChevronRight" class="size-5" aria-hidden="true" />
                </div>
            @endif
        @endisset
    </div>
</div>
