<?php

namespace Wsmallnews\Preference\Models\Concerns\Preferenceable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Wsmallnews\Member\Support\Utils as MemberUtils;
use Wsmallnews\Preference\Support\Utils as PreferenceUtils;
use Wsmallnews\User\Support\Utils as UserUtils;

trait Followable
{
    /**
     * 是否被 $preferencer 关注
     */
    public function isFollowedBy(Model $preferencer): bool
    {
        return $this->follows()
            ->withPreferencer($preferencer)
            ->snScope($this->getScopeType(), $this->getScopeId())
            ->exists();
    }

    /**
     * 是否与 $preferencer 互关
     */
    public function isMutualFollowedWith(Model $preferencer): bool
    {
        return $this->isFollowedBy($preferencer) && $preferencer->isFollowedBy($this);
    }

    /**
     * 返回 $this 的粉丝列表（关注者）仅 UserModel 类型
     */
    public function userFollowers(): MorphToMany
    {
        return $this->morphToMany(
            UserUtils::getUserModel(),           // 目标模型
            'preferenceable',         // 多态关联名
            app(PreferenceUtils::getPreferenceModel())->getTable(),            // 中间表名
            'preferenceable_id',      // 中间表的外键（指向当前模型）
            'preferencer_id'         // 中间表的另一个外键
        )
            ->wherePivot('preferencer_type', (new (UserUtils::getUserModel()))->getMorphClass())  // 过滤关注者类型
            ->wherePivot('type', '=', 'follow')      // 过滤 preference 类类型
            ->withPivot('preferencer_type', 'options')         // 带上 preference 的其他信息
            ->withTimestamps();               // 时间戳
    }

    /**
     * 返回关注 $this 的用户数量
     */
    public function userFollowerCount(): int
    {
        return $this->userFollowers()->count();
    }

    /**
     * 返回 $this 的粉丝列表（关注者）仅 MemberModel 类型
     */
    public function memberFollowers(): MorphToMany
    {
        return $this->morphToMany(
            MemberUtils::getMemberModel(),           // 目标模型
            'preferenceable',         // 多态关联名
            app(PreferenceUtils::getPreferenceModel())->getTable(),            // 中间表名
            'preferenceable_id',      // 中间表的外键（指向当前模型）
            'preferencer_id'         // 中间表的另一个外键
        )
            ->wherePivot('preferencer_type', (new (MemberUtils::getMemberModel()))->getMorphClass())  // 过滤关注者类型
            ->wherePivot('type', '=', 'follow')      // 过滤 preference 类类型
            ->withPivot('preferencer_type', 'options')         // 带上 preference 的其他信息
            ->withTimestamps();               // 时间戳
    }

    /**
     * 返回关注 $this 的成员数量
     */
    public function memberFollowerCount(): int
    {
        return $this->memberFollowers()->count();
    }

    /**
     * follows 关联
     */
    public function follows(): MorphMany
    {
        return $this->preferences()->withType('follow');
    }
}
