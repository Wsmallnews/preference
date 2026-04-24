<?php

namespace Wsmallnews\Preference\Models\Concerns\Preferencer;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Wsmallnews\Preference\Models\Concerns\Preferenceable\Viewable;
use Wsmallnews\Preference\Models\Preference;
use Wsmallnews\Preference\Support\Utils;

trait Viewer
{
    /**
     * 浏览 $preferenceable，记录浏览记录 (必须有浏览人)
     */
    public function view(Model $preferenceable): Preference
    {
        $preference = $this->views()
            ->withPreferenceable($preferenceable)
            ->snScope($preferenceable->getScopeType(), $preferenceable->getScopeId())
            ->firstOr(function () use ($preferenceable) {
                $attributes = [
                    'team_id' => current_tenant()?->id,
                    ...$preferenceable->getScopeable(),
                    'type' => 'view',
                ];

                $preference = new (Utils::getPreferenceModel());
                $preference->preferenceable()->associate($preferenceable);
                $preference->preferencer()->associate($this);
                $preference->fill($attributes)->save();

                return $preference;
            });

        if (! $preference->wasRecentlyCreated) {
            // 如果已存在，手动更新 updated_at
            $preference->touch();
        }

        // 增加浏览数量
        $preferenceable->whereKey($preferenceable->getKey())->incrementJson('counter->view_num');

        return $preference;
    }

    /**
     * 是否浏览过 $preferenceable
     */
    public function hasViewed(Model $preferenceable): bool
    {
        return $this->views()
            ->withPreferenceable($preferenceable)
            ->snScope($preferenceable->getScopeType(), $preferenceable->getScopeId())
            ->count() > 0;
    }

    /**
     * 删除浏览记录
     *
     * @return void
     */
    public function deleteView(Model $preferenceable)
    {
        return $this->views()
            ->withPreferenceable($preferenceable)
            ->snScope($preferenceable->getScopeType(), $preferenceable->getScopeId())
            ->delete();
    }

    /**
     * 清空所有浏览记录 （没限制 租户，没限制 scope）
     *
     * @param  mixed  $preferenceType
     * @return void
     */
    public function clearAllViews($preferenceType)
    {
        return $this->views()
            ->withPreferenceType($preferenceType)
            ->delete();
    }

    /**
     * 清空 scopeable 范围浏览记录 （没限制 租户）
     *
     * @param  mixed  $preferenceType
     * @return void
     */
    public function clearScopeableViews(array $scopeable, $preferenceType)
    {
        return $this->views()
            ->withPreferenceType($preferenceType)
            ->scopeable($scopeable['scope_type'], $scopeable['scope_id'])
            ->delete();
    }

    /**
     * 为 $preferenceables 附加浏览状态
     *
     * @param  mixed  $preferenceables
     */
    public function attachViewStatus(&$preferenceables, ?callable $resolver = null): mixed
    {
        $views = $this->views()->get()->keyBy(function ($item) {
            return \sprintf('%s:%s-%s:%s', $item->preferenceable_type, $item->preferenceable_id, $item->scope_type, $item->scope_id);
        });

        $attachStatus = function ($preferenceable) use ($views, $resolver) {
            $resolver = $resolver ?? fn ($m) => $m;
            $preferenceable = $resolver($preferenceable);

            if ($preferenceable && \in_array(Viewable::class, \class_uses_recursive($preferenceable))) {
                $key = \sprintf('%s:%s-%s:%s', $preferenceable->getMorphClass(), $preferenceable->getKey(), $preferenceable->getScopeType(), $preferenceable->getScopeId());
                $preferenceable->setAttribute('has_viewed', $views->has($key));
            }

            return $preferenceable;
        };

        switch (true) {
            case $preferenceables instanceof Model:
                return $attachStatus($preferenceables);
            case $preferenceables instanceof Collection:
                return $preferenceables->each($attachStatus);
            case $preferenceables instanceof LazyCollection:
                return $preferenceables = $preferenceables->map($attachStatus);
            case $preferenceables instanceof AbstractPaginator:
            case $preferenceables instanceof AbstractCursorPaginator:
                return $preferenceables->through($attachStatus);
            case $preferenceables instanceof Paginator:
                // custom paginator will return a collection
                return collect($preferenceables->items())->transform($attachStatus);
            case \is_array($preferenceables):
                return \collect($preferenceables)->transform($attachStatus);
            default:
                throw new \InvalidArgumentException('Invalid argument type.');
        }
    }

    /**
     * views 关联
     */
    public function views(): MorphMany
    {
        return $this->preferences()->withAttributes(['type' => 'view']);        // withAttributes 如果通过views 创建 preferences， type 会自动附加到 preferences 中
    }
}
