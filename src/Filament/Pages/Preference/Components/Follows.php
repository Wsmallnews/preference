<?php

namespace Wsmallnews\Preference\Filament\Pages\Preference\Components;

use Filament\Facades\Filament;
use Filament\Pages\BasePage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Livewire\WithoutUrlPagination;
use Wsmallnews\Preference\Support\Utils;
use Wsmallnews\Support\Livewire\Concerns\CanBeContained;
use Wsmallnews\Support\Livewire\Concerns\CanPagination;
use Wsmallnews\Support\Livewire\Concerns\HasAuth;
use Wsmallnews\Support\Livewire\Concerns\HasProperties;
use Wsmallnews\Support\Livewire\Concerns\Scopeable;

class Follows extends BasePage
{
    use CanBeContained;
    use CanPagination;
    use HasAuth;
    use HasProperties;
    use Scopeable;
    use WithoutUrlPagination;

    /**
     * 用户视角模型记录
     */
    public ?Model $preferencer = null;

    /**
     * 主体视角模型记录
     */
    public ?Model $preferenceable = null;

    /**
     * 关注记录列表
     */
    public Collection $follows;

    protected string $view = 'sn-preference::filament.pages.preference.components.follows';

    public function mount()
    {
        $this->hasAuthUser() || $this->authUser(Filament::auth()->user());
        $this->follows = $this->follows ?? collect([]);
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

    protected function getCurrents()
    {
        return $this->follows;
    }

    public function getViewData(): array
    {
        $query = null;
        $query = match (true) {
            $this->preferenceable => $this->preferenceable->follows()->with(['preferencer']),           // 通过当前主体视角模型记录查询
            $this->preferencer => $this->preferencer->follows()->with(['preferenceable']),               // 通过用户视角模型记录查询
            default => Utils::getPreferenceModel()::query()
                ->withType('follows')->with(['preferenceable', 'preferencer']),                   // 查询 scopeable 下所有关注记录
        };

        $query = $query->snScope(...$this->getScopeable())
            ->latest('updated_at');

        $this->follows = $this->withPagination($query);

        return [
            'paginatorLink' => $this->links,
        ];
    }
}
