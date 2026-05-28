<?php

namespace Wsmallnews\Preference\Livewire\Components;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\WithoutUrlPagination;
use Wsmallnews\Preference\Livewire\Concerns\CanManage;
use Wsmallnews\Preference\Support\Utils;
use Wsmallnews\Support\Livewire\Concerns\CanBeContained;
use Wsmallnews\Support\Livewire\Concerns\CanPagination;
use Wsmallnews\Support\Livewire\Concerns\HasAuth;
use Wsmallnews\Support\Livewire\Concerns\HasProperties;

class Views extends Base implements HasActions, HasSchemas
{
    use CanBeContained;
    use CanManage;
    use CanPagination;
    use HasAuth;
    use HasProperties;
    use InteractsWithActions;
    use InteractsWithSchemas;
    use WithoutUrlPagination;

    public ?Model $preferencer = null;

    public ?Model $preferenceable = null;

    public bool $manageable = false;

    public Collection $views;

    public string $listType;

    public function mount(): void
    {
        $this->hasAuthUser() || $this->authUser(Filament::auth()->user());
        $this->views = $this->views ?? collect([]);

        $this->listType = match (true) {
            filled($this->preferenceable) => 'preferenceable',
            filled($this->preferencer) => 'preferencer',
            default => 'preference',
        };
    }

    public function getEmptyLabel(): ?string
    {
        if ($this->preferencer) {
            return $this->getProperty('emptyLabel', __('sn-preference::preference.components.views_empty_heading_user'));
        }

        return $this->getProperty('emptyLabel', __('sn-preference::preference.components.views_empty_heading_subject'));
    }

    public function getEmptyTipLabel(): ?string
    {
        if ($this->preferencer) {
            return $this->getProperty('emptyTipLabel', __('sn-preference::preference.components.views_empty_description_user'));
        }

        return $this->getProperty('emptyTipLabel', __('sn-preference::preference.components.views_empty_description_subject'));
    }

    #[Computed]
    public function getCount(): int
    {
        return $this->getQuery()->count();
    }

    public function deleteViewAction(): Action
    {
        return Action::make('deleteView')
            ->label(__('sn-preference::preference.action.delete_view'))
            ->icon('heroicon-m-trash')
            ->color('danger')
            ->size('sm')
            ->link()
            ->requiresConfirmation()
            ->modalHeading(__('sn-preference::preference.action.delete_view_heading'))
            ->modalDescription(__('sn-preference::preference.action.delete_view_description'))
            ->visible(fn (): bool => $this->isManageable())
            ->action(function (array $arguments) {
                $preference = Utils::getPreferenceModel()::query()
                    ->withType('view')
                    ->snScope(...$this->getScopeable())
                    ->findOrFail($arguments['key']);
                $preference->delete();

                $this->views = $this->views->filter(fn ($item) => $item->id !== (int) $arguments['key']);

                Notification::make()
                    ->title(__('sn-preference::preference.action.delete_view_success'))
                    ->success()
                    ->send();
            });
    }

    public function batchDeleteAction(): Action
    {
        return Action::make('batchDelete')
            ->label(__('sn-preference::preference.action.delete_selected', ['count' => $this->getSelectedCount()]))
            ->icon('heroicon-m-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading(__('sn-preference::preference.action.batch_delete_view_heading', ['count' => $this->getSelectedCount()]))
            ->visible(fn (): bool => $this->isManageable() && $this->getSelectedCount() > 0)
            ->action(function () {
                Utils::getPreferenceModel()::query()
                    ->withType('view')
                    ->snScope(...$this->getScopeable())
                    ->whereIn('id', $this->selected)
                    ->delete();

                $this->views = $this->views->filter(fn ($item) => ! in_array($item->id, $this->selected));

                $count = count($this->selected);
                $this->selected = [];

                Notification::make()
                    ->title(__('sn-preference::preference.action.batch_delete_view_success', ['count' => $count]))
                    ->success()
                    ->send();
            });
    }

    public function isManageable(): bool
    {
        return $this->manageable && $this->listType === 'preferencer';
    }

    public function render()
    {
        $query = $this->getQuery();

        $query = $query->latest('updated_at');

        $this->views = $this->withPagination($query);

        return view('sn-preference::livewire.components.views', [
            'paginatorLink' => $this->links,
        ]);
    }

    protected function getCurrents()
    {
        return $this->views;
    }

    protected function getQuery()
    {
        $query = match (true) {
            filled($this->preferenceable) => $this->preferenceable->views()->with(['preferencer']),
            filled($this->preferencer) => $this->preferencer->views()->with(['preferenceable']),
            default => Utils::getPreferenceModel()::query()
                ->withType('view')->with(['preferenceable', 'preferencer']),
        };

        return $query->snScope(...$this->getScopeable());
    }
}
