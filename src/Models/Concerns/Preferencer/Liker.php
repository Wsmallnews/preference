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

        return $preference;
    }


    public function unlike(Model $preferenceable): bool
    {
        $preference = $this->likes()
            ->withPreferenceable($preferenceable)
            ->snScope($preferenceable->getScopeType(), $preferenceable->getScopeId())
            ->first();

        if ($preference) {
            return $preference->delete();
        }

        return true;
    }

    /**
     * @return Like|null
     *
     * @throws \Exception
     */
    public function toggleLike(Model $object)
    {
        return $this->hasLiked($object) ? $this->unlike($object) : $this->like($object);
    }

    public function hasLiked(Model $preferenceable): bool
    {
        return $this->likes()
            ->withPreferenceable($preferenceable)
            ->snScope($preferenceable->getScopeType(), $preferenceable->getScopeId())
            ->count() > 0;
    }

    public function likes(): MorphMany
    {
        return $this->preferences()->withAttributes(['type' => 'like']);        // withAttributes 如果通过likes 创建 preferences， type 会自动附加到 preferences 中
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
}
