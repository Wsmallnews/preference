<?php

namespace Wsmallnews\Preference\Models\Concerns\Preferencer;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Wsmallnews\Preference\Models\Concerns\Preferenceable\Likeable;
use Wsmallnews\Preference\Models\Preference;
use Wsmallnews\Preference\Support\Utils;

trait Liker
{
    /**
     * 喜欢 $preferenceable
     */
    public function like(Model $preferenceable): Preference
    {
        $preference = $this->likes()
            ->withPreferenceable($preferenceable)
            ->snScope($preferenceable->getScopeType(), $preferenceable->getScopeId())
            ->firstOr(function () use ($preferenceable) {
                $attributes = [
                    'team_id' => current_tenant()?->id,
                    ...$preferenceable->getScopeable(),
                    'type' => 'like',
                ];

                $preference = new (Utils::getPreferenceModel());
                $preference->preferenceable()->associate($preferenceable);
                $preference->preferencer()->associate($this);
                $preference->fill($attributes)->save();

                return $preference;
            });

        if ($preference->wasRecentlyCreated) {
            // 增加喜欢数量
            $preferenceable->whereKey($preferenceable->getKey())->incrementJson('counter->like_num');
        }

        return $preference;
    }

    /**
     * 取消喜欢 $preferenceable
     */
    public function unlike(Model $preferenceable): bool
    {
        $preference = $this->likes()
            ->withPreferenceable($preferenceable)
            ->snScope($preferenceable->getScopeType(), $preferenceable->getScopeId())
            ->first();

        if ($preference) {
            // 减少喜欢数量
            $preferenceable->whereKey($preferenceable->getKey())->decrementJson('counter->like_num');
            // 删除喜欢记录
            return $preference->delete();
        }

        return true;
    }

    /**
     * 切换喜欢状态
     *
     * @param  Model  $object
     * @return Preference
     */
    public function toggleLike(Model $preferenceable)
    {
        return $this->hasLiked($preferenceable) ? $this->unlike($preferenceable) : $this->like($preferenceable);
    }

    /**
     * 是否喜欢 $preferenceable
     */
    public function hasLiked(Model $preferenceable): bool
    {
        return $this->likes()
            ->withPreferenceable($preferenceable)
            ->snScope($preferenceable->getScopeType(), $preferenceable->getScopeId())
            ->count() > 0;
    }

    /**
     * Get Query Builder for likes
     *
     * @return Builder
     */
    // public function getLikedItems(string $model)
    // {
    //     return app($model)->whereHas(
    //         'likers',
    //         function ($q) {
    //             return $q->where(config('like.user_foreign_key'), $this->getKey());
    //         }
    //     );
    // }

    /**
     * 为 $preferenceables 附加喜欢状态
     *
     * @param  mixed  $preferenceables
     * @return mixed
     */
    public function attachLikeStatus(&$preferenceables, ?callable $resolver = null)
    {
        $likes = $this->likes()->get()->keyBy(function ($item) {
            return \sprintf('%s:%s-%s:%s', $item->preferenceable_type, $item->preferenceable_id, $item->scope_type, $item->scope_id);
        });

        $attachStatus = function ($preferenceable) use ($likes, $resolver) {
            $resolver = $resolver ?? fn ($m) => $m;
            $preferenceable = $resolver($preferenceable);

            if ($preferenceable && \in_array(Likeable::class, \class_uses_recursive($preferenceable))) {
                $key = \sprintf('%s:%s-%s:%s', $preferenceable->getMorphClass(), $preferenceable->getKey(), $preferenceable->getScopeType(), $preferenceable->getScopeId());
                $preferenceable->setAttribute('has_liked', $likes->has($key));
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

    // protected function totalLikes(): Attribute
    // {
    //     return Attribute::make(get: function ($value) {
    //         return $this->likes()->count() ?? 0;
    //     });
    // }

    /**
     * likes 关联
     */
    public function likes(): MorphMany
    {
        return $this->preferences()->withAttributes(['type' => 'like']);        // withAttributes 如果通过likes 创建 preferences， type 会自动附加到 preferences 中
    }
}
