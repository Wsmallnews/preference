<?php

namespace Wsmallnews\Preference\Filament\Pages\Preference\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Reactive;
use Wsmallnews\Support\Livewire\Concerns\CanBeContained;
use Wsmallnews\Support\Livewire\Concerns\HasProperties;
use Wsmallnews\Support\Livewire\Concerns\Scopeable;

class Views extends Widget
{
    use CanBeContained;
    use HasProperties;
    use Scopeable;

    #[Reactive]
    public ?Model $record = null;

    /**
     * preferencer = 用户视角 | preferenceable = 主体视角
     */
    public string $widgetType = 'preferenceable';

    protected int | string | array $columnSpan = 'full';

    protected string $view = 'sn-preference::filament.pages.preference.widgets.views';

    public function getViewData(): array
    {
        return [
            'preferenceable' => $this->widgetType == 'preferenceable' ? $this->record : null,
            'preferencer' => $this->widgetType == 'preferencer' ? $this->record : null,
        ];
    }
}
