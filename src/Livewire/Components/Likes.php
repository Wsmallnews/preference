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

class Likes extends Base implements HasActions, HasSchemas
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

    public Collection $likes;

    public string $listType;

    public function mount(): void
    {
        $this->hasAuthUser() || $this->authUser(Filament::auth()->user());
        $this->likes = $this->likes ?? collect([]);

        $this->listType = match (true) {
            filled($this->preferenceable) => 'preferenceable',
            filled($this->preferencer) => 'preferencer',
            default => 'preference',
        };
    }

    public function getEmptyLabel(): ?string
    {
        if ($this->preferencer) {
            return $this->getProperty('emptyLabel', __('sn-preference::preference.components.likes_empty_heading_user'));
        }

        return $this->getProperty('emptyLabel', __('sn-preference::preference.components.likes_empty_heading_subject'));
    }

    public function getEmptyTipLabel(): ?string
    {
        if ($this->preferencer) {
            return $this->getProperty('emptyTipLabel', __('sn-preference::preference.components.likes_empty_description_user'));
        }

        return $this->getProperty('emptyTipLabel', __('sn-preference::preference.components.likes_empty_description_subject'));
    }

    #[Computed]
    public function getCount(): int
    {
        return $this->getQuery()->count();
    }

    public function unlikeAction(): Action
    {
        return Action::make('unlike')
            ->label(__('sn-preference::preference.action.unlike'))
            ->icon('heroicon-m-heart')
            ->color('danger')
            ->size('sm')
            ->link()
            ->requiresConfirmation()
            ->modalHeading(__('sn-preference::preference.action.unlike_heading'))
            ->visible(fn (): bool => $this->isManageable())
            ->action(function (array $arguments) {
                $preference = Utils::getPreferenceModel()::findOrFail($arguments['key']);
                $preference->delete();

                $this->likes = $this->likes->filter(fn ($item) => $item->id !== (int) $arguments['key']);

                Notification::make()
                    ->title(__('sn-preference::preference.action.unlike_success'))
                    ->success()
                    ->send();
            });
    }

    public function batchUnlikeAction(): Action
    {
        return Action::make('batchUnlike')
            ->label(__('sn-preference::preference.action.unlike_selected', ['count' => $this->getSelectedCount()]))
            ->icon('heroicon-m-heart')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading(__('sn-preference::preference.action.batch_unlike_heading', ['count' => $this->getSelectedCount()]))
            ->visible(fn (): bool => $this->isManageable() && $this->getSelectedCount() > 0)
            ->action(function () {
                Utils::getPreferenceModel()::whereIn('id', $this->selected)->delete();

                $this->likes = $this->likes->filter(fn ($item) => ! in_array($item->id, $this->selected));

                $count = count($this->selected);
                $this->selected = [];

                Notification::make()
                    ->title(__('sn-preference::preference.action.batch_unlike_success', ['count' => $count]))
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

        $this->likes = $this->withPagination($query, $this->getFingerprint());

        return view('sn-preference::livewire.components.likes', [
            'paginatorLink' => $this->links,
        ]);
    }

    protected function getCurrents()
    {
        return $this->likes;
    }

    /**
     * 获取分页查询指纹，用于 CanPagination 的缓存失效检测。
     */
    protected function getFingerprint(): string
    {
        return md5(serialize([
            'listType' => $this->listType,
            'preferencer' => $this->preferencer?->getKey(),
            'preferenceable' => $this->preferenceable?->getKey(),
            ...$this->getScopeable(),
        ]));
    }


    protected function getQuery()
    {
        $query = match (true) {
            filled($this->preferenceable) => $this->preferenceable->likes()->with(['preferencer']),
            filled($this->preferencer) => $this->preferencer->likes()->with(['preferenceable']),
            default => Utils::getPreferenceModel()::query()
                ->withType('like')->with(['preferenceable', 'preferencer']),
        };

        return $query->snScope(...$this->getScopeable());
    }
}
