<?php

namespace Wsmallnews\Preference\Models\Concerns\Preferenceable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Wsmallnews\Preference\Support\Utils as PreferenceUtils;
use Wsmallnews\User\Support\Utils as UserUtils;

trait Viewable
{
    /**
     * 是否被 $preferencer 浏览过
     */
    public function isViewedBy(Model $preferencer): bool
    {
        return $this->views()
            ->withPreferencer($preferencer)
            ->snScope($this->getScopeType(), $this->getScopeId())
            ->exists();
    }

    /**
     * 返回 $this 被浏览过的用户列表 仅 preferencer_type 仅 UserModel 类型
     */
    public function userViewers(): MorphToMany
    {
        return $this->morphToMany(
            UserUtils::getUserModel(),           // 目标模型
            'preferenceable',         // 多态关联名
            app(PreferenceUtils::getPreferenceModel())->getTable(),            // 中间表名
            'preferenceable_id',      // 中间表的外键（指向当前模型）
            'preferencer_id'         // 中间表的另一个外键
        )
            ->wherePivot('preferencer_type', (new (UserUtils::getUserModel()))->getMorphClass())  // 过滤评论发布者类型
            ->wherePivot('type', '=', 'view')      // 过滤 preference 类类型
            ->withPivot('preferencer_type', 'options')         // 带上 preference 的其他信息
            ->withTimestamps();               // 时间戳
    }

    /**
     * views 关联
     */
    public function views(): MorphMany
    {
        return $this->preferences()->withType('view');
    }
}
