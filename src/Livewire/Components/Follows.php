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

class Follows extends Base implements HasActions, HasSchemas
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

    public bool $canManage = false;

    public Collection $follows;

    public string $listType;

    public function mount(): void
    {
        $this->hasAuthUser() || $this->authUser(Filament::auth()->user());
        $this->follows = $this->follows ?? collect([]);

        $this->listType = match (true) {
            filled($this->preferenceable) => 'preferenceable',
            filled($this->preferencer) => 'preferencer',
            default => 'preference',
        };
    }

    public function getEmptyLabel(): ?string
    {
        if ($this->preferencer) {
            return $this->getProperty('emptyLabel', __('sn-preference::preference.components.follows_empty_heading_user'));
        }

        return $this->getProperty('emptyLabel', __('sn-preference::preference.components.follows_empty_heading_subject'));
    }

    public function getEmptyTipLabel(): ?string
    {
        if ($this->preferencer) {
            return $this->getProperty('emptyTipLabel', __('sn-preference::preference.components.follows_empty_description_user'));
        }

        return $this->getProperty('emptyTipLabel', __('sn-preference::preference.components.follows_empty_description_subject'));
    }

    #[Computed]
    public function getCount(): int
    {
        return $this->getQuery()->count();
    }

    public function getViewData(): array
    {
        $query = $this->getQuery();

        $query = $query->snScope(...$this->getScopeable())
            ->latest('updated_at');

        $this->follows = $this->withPagination($query);

        return [
            'paginatorLink' => $this->links,
        ];
    }

    protected function getCurrents()
    {
        return $this->follows;
    }

    public function unfollowAction(): Action
    {
        return Action::make('unfollow')
            ->label(__('sn-preference::preference.action.unfollow'))
            ->color('gray')
            ->size('sm')
            ->requiresConfirmation()
            ->modalHeading(__('sn-preference::preference.action.unfollow_heading'))
            ->visible(fn (): bool => $this->canManage())
            ->action(function (array $arguments) {
                $preference = Utils::getPreferenceModel()::findOrFail($arguments['key']);
                $preference->delete();

                $this->follows = $this->follows->filter(fn ($item) => $item->id !== (int) $arguments['key']);

                Notification::make()
                    ->title(__('sn-preference::preference.action.unfollow_success'))
                    ->success()
                    ->send();
            });
    }

    public function batchUnfollowAction(): Action
    {
        return Action::make('batchUnfollow')
            ->label(__('sn-preference::preference.action.unfollow_selected', ['count' => $this->getSelectedCount()]))
            ->icon('heroicon-m-user-minus')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading(__('sn-preference::preference.action.batch_unfollow_heading', ['count' => $this->getSelectedCount()]))
            ->visible(fn (): bool => $this->canManage() && $this->getSelectedCount() > 0)
            ->action(function () {
                Utils::getPreferenceModel()::whereIn('id', $this->selected)->delete();

                $this->follows = $this->follows->filter(fn ($item) => ! in_array($item->id, $this->selected));

                $count = count($this->selected);
                $this->selected = [];

                Notification::make()
                    ->title(__('sn-preference::preference.action.batch_unfollow_success', ['count' => $count]))
                    ->success()
                    ->send();
            });
    }

    public function canManage(): bool
    {
        return $this->canManage && $this->listType === 'preferencer';
    }

    protected function getQuery()
    {
        return match (true) {
            filled($this->preferenceable) => $this->preferenceable->follows()->with(['preferencer']),
            filled($this->preferencer) => $this->preferencer->follows()->with(['preferenceable']),
            default => Utils::getPreferenceModel()::query()
                ->withType('follow')->with(['preferenceable', 'preferencer']),
        };
    }

    public function render()
    {
        return view('sn-preference::livewire.components.follows', $this->getViewData());
    }
}
