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
                    {{ __('sn-preference::preference.components.likes_title_user') }}
                @elseif ($listType == 'preferenceable')
                    {{ __('sn-preference::preference.components.likes_title_subject') }}
                @else
                    {{ __('sn-preference::preference.components.likes_title') }}
                @endif
            </h3>
            <div class="flex items-center gap-2 sn-tip-text">
                <x-filament::icon icon="heroicon-m-heart" class="size-4" />
                <span>{{ $likes->count() }}</span>
            </div>
        </div>

        @if ($likes->isNotEmpty())
            <x-sn-support::paginators.container
                class="w-full flex flex-col gap-4"
                :page-type="$pageType"
                :page-info="$pageInfo"
                :paginator-link="$paginatorLink"
                :page-name="$pageName"
            >
                @foreach ($likes as $preference)
                    @if ($listType == 'preferenceable')
                        <x-sn-preference::preferencer :preference="$preference" :preferencer="$preference->preferencer" contained />
                    @elseif ($listType == 'preferencer')
                        <x-sn-preference::preferenceable :preference="$preference" :preferenceable="$preference->preferenceable" contained />
                    @else
                        <x-sn-preference::preference :preference="$preference" contained />
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
