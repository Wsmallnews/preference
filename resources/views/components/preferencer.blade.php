@props([
    'preference',
    'preferencer',
    'contained' => false,
    'hasLink' => false,
])

@php
    use Filament\Support\Icons\Heroicon;
    use Wsmallnews\Support\Contracts\HasSnIdentifiable;
    use Wsmallnews\Preference\Exceptions\PreferenceException;

    if (! $preferencer instanceof HasSnIdentifiable) {
        throw new PreferenceException(get_class($preferencer) . ' model must implement `\Wsmallnews\Support\Contracts\HasSnIdentifiable` interface.');
    }

    $rawHrefUrl = $preferencer->getSnHrefUrl();
    $href = $rawHrefUrl ? (string) $rawHrefUrl : '';
    $tag = ($hasLink && $href !== '') ? 'a' : 'div';

    $timeLabel = match ($preference->type) {
        'follow' => __('sn-preference::preference.widget.followed_at', ['time' => $preference->created_at->diffForHumans()]),
        'like' => __('sn-preference::preference.widget.liked_at', ['time' => $preference->created_at->diffForHumans()]),
        default => __('sn-preference::preference.widget.viewed_at', ['time' => $preference->created_at->diffForHumans()]),
    };
@endphp

<{{ $tag }}
    {{
        $attributes->class([
            'sn-container p-4' => $contained,
            'sn-hover sn-link' => $hasLink,
            'flex items-center gap-4 justify-between group',
        ])
    }}
    {{ ($tag === 'a') ? \Filament\Support\generate_href_html($href) : '' }}
    @if ($hasLink && $href === '')
        wire:click.stop="$dispatch('sn-preference-preferencer-click', { preference: {{ $preference->id }} })"
    @endif
>
    <div class="w-12 h-12 rounded-full shrink-0 overflow-hidden bg-gray-100 dark:bg-gray-800">
        @if ($preferencer->getSnAvatarUrl())
            <img class="w-full h-full object-cover" src="{{ files_url($preferencer->getSnAvatarUrl()) }}" alt="{{ $preferencer->getSnName() }}" />
        @else
            <div class="sn-image-placeholder">
                <x-filament::icon :icon="Heroicon::User" class="w-full h-full" aria-hidden="true" />
            </div>
        @endif
    </div>

    <div class="flex flex-col grow gap-1 min-w-0">
        <div class="flex items-center gap-2">
            <span class="sn-content-text">
                {{ $preferencer->getSnName() }}
            </span>

            @isset($badge)
                {{ $badge }}
            @endisset
        </div>

        <span class="sn-descript-text sn-truncate-2">
            {{ $preferencer->getSnEmail() }}
        </span>

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
            @if ($hasLink)
                <div class="sn-gray-text shrink-0 hidden group-hover:block">
                    <x-filament::icon :icon="Heroicon::ChevronRight" class="size-5" aria-hidden="true" />
                </div>
            @endif
        @endisset
    </div>
</{{ $tag }}>
