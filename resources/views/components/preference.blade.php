@props([
    'preference',
    'contained' => false,
    'hasLink' => false,
    'embedded' => false,
    'href' => null,
])

@php
    use Filament\Support\Icons\Heroicon;
    use Wsmallnews\Support\Contracts\HasSnSubject;
    use Wsmallnews\Support\Contracts\HasSnIdentifiable;
    use Wsmallnews\Preference\Exceptions\PreferenceException;

    $preferencer = $preference->preferencer;
    $preferenceable = $preference->preferenceable;

    if ($preferencer && ! $preferencer instanceof HasSnIdentifiable) {
        throw new PreferenceException(get_class($preferencer) . ' model must implement `\Wsmallnews\Support\Contracts\HasSnIdentifiable` interface.');
    }
    if ($preferenceable && ! $preferenceable instanceof HasSnSubject) {
        throw new PreferenceException(get_class($preferenceable) . ' model must implement `\Wsmallnews\Support\Contracts\HasSnSubject` interface.');
    }

    // 跳转链接由调用方直传（string|\Closure）：闭包优先接收 preferenceable，缺失时接收 preferencer；
    // 未传则不渲染链接（点击分发事件，由调用方监听跳转）
    $rawHrefUrl = $href instanceof \Closure ? $href($preferenceable ?? $preferencer) : $href;

    // panel 语境下未传入链接时，兜底后台资源链接
    if (blank($rawHrefUrl) && is_in_panel() && filled($preferenceable ?? $preferencer)) {
        $rawHrefUrl = \Wsmallnews\Support\Helpers\FilamentModelHelper::getUrl($preferenceable ?? $preferencer);
    }

    $href = $rawHrefUrl ? (string) $rawHrefUrl : '';
    $tag = ($hasLink && $href !== '') ? 'a' : 'div';

    $timeLabel = match ($preference->type) {
        'follow' => __('sn-preference::preference.widget.followed_at', ['time' => $preference->created_at->diffForHumans()]),
        'like' => __('sn-preference::preference.widget.liked_at', ['time' => $preference->created_at->diffForHumans()]),
        default => __('sn-preference::preference.widget.viewed_at', ['time' => $preference->created_at->diffForHumans()]),
    };

    $typeBadgeLabel = match ($preference->type) {
        'follow' => __('sn-preference::preference.components.follow'),
        'like' => __('sn-preference::preference.components.like'),
        'view' => __('sn-preference::preference.components.view'),
        default => $preference->type,
    };
@endphp

<{{ $tag }}
    {{
        $attributes->class([
            'sn-container' => $contained,
            'sn-hover sn-link' => $hasLink && ! $embedded,
            'flex flex-col group',
        ])
    }}
    {{ ($tag === 'a') ? \Filament\Support\generate_href_html($href) : '' }}
    @if ($hasLink && $href === '')
        wire:click.stop="$dispatch('sn-preference-preference-click', { preference: {{ $preference->id }} })"
    @endif
>
    {{-- 上排: preferencer 操作者 --}}
    @if ($preferencer)
        <div class="flex items-center gap-4 px-4 pt-4 pb-3">
            <div class="w-8 h-8 rounded-full shrink-0 overflow-hidden bg-gray-100 dark:bg-gray-800">
                @if ($preferencer->getSnAvatarUrl())
                    <img class="w-full h-full object-cover" src="{{ files_url($preferencer->getSnAvatarUrl()) }}" alt="{{ $preferencer->getSnName() }}" />
                @else
                    <div class="sn-image-placeholder">
                        <x-filament::icon :icon="Heroicon::User" class="size-4" aria-hidden="true" />
                    </div>
                @endif
            </div>

            <div class="flex flex-col grow gap-0.5 min-w-0">
                <div class="flex items-center gap-2">
                    <span class="sn-content-text text-sm">
                        {{ $preferencer->getSnName() }}
                    </span>

                    @isset($typeBadge)
                        {{ $typeBadge }}
                    @else
                        <span @class([
                            'inline-flex items-center shrink-0 text-[10px] font-semibold px-1.5 py-px rounded',
                            'bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300' => $preference->type === 'follow',
                            'bg-pink-50 text-pink-700 dark:bg-pink-950 dark:text-pink-300' => $preference->type === 'like',
                            'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' => $preference->type === 'view',
                        ])>
                            {{ $typeBadgeLabel }}
                        </span>
                    @endisset
                </div>

                @if ($preferencer->getSnEmail())
                    <span class="sn-descript-text text-xs sn-truncate">
                        {{ $preferencer->getSnEmail() }}
                    </span>
                @endif

                @isset($extra)
                    <div class="sn-tip-text text-[10px]">
                        {{ $extra }}
                    </div>
                @endisset
            </div>

            <div class="flex items-center gap-2 shrink-0">
                @isset($meta)
                    {{ $meta }}
                @else
                    <div class="sn-tip-text">
                        {{ $timeLabel }}
                    </div>
                    @if ($hasLink)
                        <div class="sn-gray-text hidden group-hover:block">
                            <x-filament::icon :icon="Heroicon::ChevronRight" class="size-5" aria-hidden="true" />
                        </div>
                    @endif
                @endisset
            </div>
        </div>
    @endif

    {{-- 分隔线 --}}
    @if ($preferencer && $preferenceable)
        <div class="border-t border-gray-200 dark:border-gray-700 mx-4"></div>
    @endif

    {{-- 下排: preferenceable 被操作对象 --}}
    @if ($preferenceable)
        <div class="flex items-center gap-4 px-4 py-3">
            <div class="w-9 h-9 shrink-0 overflow-hidden rounded-md bg-gray-100 dark:bg-gray-800">
                @if ($preferenceable->getSnSubjectCoverUrl())
                    <img class="w-full h-full object-cover" src="{{ files_url($preferenceable->getSnSubjectCoverUrl()) }}" alt="{{ $preferenceable->getSnSubjectTitle() }}" />
                @else
                    <div class="sn-image-placeholder rounded-md">
                        <x-filament::icon :icon="Heroicon::Photo" class="size-4" aria-hidden="true" />
                    </div>
                @endif
            </div>

            <div class="flex flex-col grow gap-0.5 min-w-0">
                <div class="flex items-center gap-2">
                    @if ($preferenceable->getSnSubjectTitle())
                        <span class="sn-content-text text-sm sn-truncate">
                            {{ $preferenceable->getSnSubjectTitle() }}
                        </span>
                    @endif

                    @isset($badge)
                        {{ $badge }}
                    @endisset
                </div>

                @if ($preferenceable->getSnSubjectDescription())
                    <span class="sn-descript-text text-xs sn-truncate-2">
                        {{ $preferenceable->getSnSubjectDescription() }}
                    </span>
                @endif
            </div>

            {{-- 下排右侧占位，和上排时间区域宽度对齐 --}}
            <div class="w-[44px] shrink-0"></div>
        </div>
    @endif
</{{ $tag }}>
