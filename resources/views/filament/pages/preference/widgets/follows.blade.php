<x-filament-widgets::widget>
    <livewire:sn-preference-fi-follows
        :properties="$this->getProperties()"
        :scope-type="$scopeType" :scope-id="$scopeId"
        :preferenceable="$preferenceable"
        :preferencer="$preferencer"
        page-name="view-page"
        page-type="paginator"
        :contained="$contained"
        :key="'fi-components-sn-preference-follows:' . $widgetType . ':' . $record->id" />
</x-filament-widgets::widget>