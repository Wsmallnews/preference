<?php

namespace Wsmallnews\Preference\Filament\Pages\Preference\Components;

use Filament\Facades\Filament;
use Filament\Pages\BasePage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\WithoutUrlPagination;
use Wsmallnews\Preference\Support\Utils;
use Wsmallnews\Support\Livewire\Concerns\CanBeContained;
use Wsmallnews\Support\Livewire\Concerns\CanPagination;
use Wsmallnews\Support\Livewire\Concerns\HasAuth;
use Wsmallnews\Support\Livewire\Concerns\HasProperties;
use Wsmallnews\Support\Livewire\Concerns\Scopeable;

class Views extends BasePage
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
     * 浏览记录列表
     */
    public Collection $views;

    /**
     * 浏览记录列表类型 preferencer | preferenceable | preference
     */
    public string $listType;

    protected string $view = 'sn-preference::filament.pages.preference.components.views';

    public function mount()
    {
        $this->hasAuthUser() || $this->authUser(Filament::auth()->user());
        $this->views = $this->views ?? collect([]);

        $this->listType = match (true) {
            filled($this->preferencer) => 'preferencer',
            filled($this->preferenceable) => 'preferenceable',
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
        $query = $this->getQuery();

        return $query->count();
    }

    public function getViewData(): array
    {
        $query = $this->getQuery();

        $query = $query->snScope(...$this->getScopeable())
            ->latest('updated_at');

        $this->views = $this->withPagination($query);

        return [
            'paginatorLink' => $this->links,
        ];
    }

    protected function getCurrents()
    {
        return $this->views;
    }


    /**
     * 获取浏览记录查询对象
     */
    protected function getQuery()
    {
        return match (true) {
            filled($this->preferenceable) => $this->preferenceable->views()->with(['preferencer']),           // 通过当前主体视角模型记录查询
            filled($this->preferencer) => $this->preferencer->views()->with(['preferenceable']),               // 通过用户视角模型记录查询
            default => Utils::getPreferenceModel()::query()
                ->withType('view')->with(['preferenceable', 'preferencer']),                   // 查询 scopeable 下所有浏览记录
        };
    }
}
