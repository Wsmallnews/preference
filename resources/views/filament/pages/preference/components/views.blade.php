@php
    use Filament\Support\Icons\Heroicon;

    $listType = $preferencer ? 'preferencer' : 'preferenceable';
@endphp

<div class="w-full">
    <div @class([
        'sn-container px-4 py-8' => $contained,
        'w-full flex flex-col gap-4',
    ])>
        <div class="flex items-center justify-between">
            <h3 class="sn-h3-text">
                @if ($listType == 'preferencer')
                    {{ __('sn-preference::preference.components.views_title_user') }}
                @else
                    {{ __('sn-preference::preference.components.views_title_subject') }}
                @endif
            </h3>
        </div>

        @if ($views->isNotEmpty())
            <x-sn-support::paginators.container
                class="w-full flex flex-col gap-4"
                :page-type="$pageType"
                :page-info="$pageInfo"
                :paginator-link="$paginatorLink"
                :page-name="$pageName"
            >
                @foreach($views as $preference)
                    @if ($listType == 'preferenceable')
                        @php
                            $preferencer = $preference->preferencer;
                        @endphp

                        <x-sn-support::identifiable :identifiable="$preferencer" />
                    @else
                    @endif
                @endforeach
            </x-sn-support::paginators.container>
        @else
            <x-filament::empty-state
                :contained="false"
                icon="heroicon-m-eye"
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