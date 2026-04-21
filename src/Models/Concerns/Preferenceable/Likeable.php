<?php

namespace Wsmallnews\Preference\Models\Concerns\Preferenceable;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Wsmallnews\Preference\Support\Utils as PreferenceUtils;
use Wsmallnews\User\Support\Utils as UserUtils;

trait Likeable
{
    public function isLikedBy(Model $preferencer): bool
    {
        return $this->likes()
            ->withPreferencer($preferencer)
            ->snScope($this->getScopeType(), $this->getScopeId())
            ->exists();
    }

    /**
     * Return UserModel likers
     */
    public function userLikers(): MorphToMany
    {
        return $this->morphToMany(
            UserUtils::getUserModel(),           // 目标模型
            'preferenceable',         // 多态关联名
            app(PreferenceUtils::getPreferenceModel())->getTable(),            // 中间表名
            'preferenceable_id',      // 中间表的外键（指向当前模型）
            'preferencer_id'         // 中间表的另一个外键
        )
            ->wherePivot('preferencer_type', (new (UserUtils::getUserModel()))->getMorphClass())  // 过滤评论发布者类型
            ->wherePivot('type', '=', 'like')      // 过滤 preference 类类型
            ->withPivot('preferencer_type', 'options')         // 带上 preference 的其他信息
            ->withTimestamps();               // 时间戳
    }

    public function likes(): MorphMany
    {
        return $this->preferences()->withType('like');
    }

    // protected function totalLikers(): Attribute
    // {
    //     return Attribute::get(function () {
    //         return $this->likers()->count() ?? 0;
    //     });
    // }
}
