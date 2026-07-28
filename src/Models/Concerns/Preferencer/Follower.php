<?php

namespace Wsmallnews\Preference\Models\Concerns\Preferencer;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use InvalidArgumentException;
use Wsmallnews\Member\Support\Utils as MemberUtils;
use Wsmallnews\Preference\Models\Concerns\Preferenceable\Followable;
use Wsmallnews\Preference\Models\Preference;
use Wsmallnews\Preference\Support\Utils;
use Wsmallnews\User\Support\Utils as UserUtils;

trait Follower
{
    /**
     * 关注 $preferenceable
     */
    public function follow(Model $preferenceable): Preference
    {
        if ($preferenceable->is($this)) {
            throw new InvalidArgumentException('Cannot follow yourself.');
        }

        if (! in_array(Followable::class, class_uses($preferenceable))) {
            throw new InvalidArgumentException('The preferenceable model must use the Followable trait.');
        }

        $preference = $this->follows()
            ->withPreferenceable($preferenceable)
            ->snScope($preferenceable->getScopeType(), $preferenceable->getScopeId())
            ->firstOr(function () use ($preferenceable) {
                $attributes = [
                    'team_id' => current_tenant()?->id,
                    ...$preferenceable->getScopeable(),
                    'type' => 'follow',
                ];

                $preference = new (Utils::getPreferenceModel());
                $preference->preferenceable()->associate($preferenceable);
                $preference->preferencer()->associate($this);
                $preference->fill($attributes)->save();

                return $preference;
            });

        if ($preference->wasRecentlyCreated) {
            // 增加自己的关注数量
            $this->whereKey($this->getKey())->incrementJson('counter->follow_num');

            // 增加对方的被关注数
            $preferenceable->whereKey($preferenceable->getKey())->incrementJson('counter->followed_num');

            // 只有 A 确实关注了 B，才去更新 B 关注 A 的记录中的 followed_at
            // 如果 B 已关注 A，说明是互关状态，需要记录互关时间
            if ($preferenceable->isFollowing($this)) {
                $this->updateFollowedAt($preferenceable, now()->toDateTimeString());
            }
        }

        return $preference;
    }

    /**
     * 更新对方关注记录中的 followed_at 字段
     *
     * @param  Model  $target  被关注的对象
     * @param  string|null  $followedAt  关注时间，null 表示取消
     */
    protected function updateFollowedAt(Model $target, ?string $followedAt): void
    {
        $target->follows()
            ->withPreferenceable($this)
            ->snScope($this->getScopeType(), $this->getScopeId())
            ->update([
                'options' => \DB::raw("JSON_SET(options, '$.followed_at', " . ($followedAt ? "'{$followedAt}'" : 'null') . ')'),
            ]);
    }

    /**
     * 取消关注 $preferenceable
     */
    public function unfollow(Model $preferenceable): bool
    {
        $preference = $this->follows()
            ->withPreferenceable($preferenceable)
            ->snScope($preferenceable->getScopeType(), $preferenceable->getScopeId())
            ->first();

        if ($preference) {
            // 减少自己的关注数量
            $this->whereKey($this->getKey())->decrementJson('counter->follow_num');

            // 减少对方的被关注数
            $preferenceable->whereKey($preferenceable->getKey())->decrementJson('counter->followed_num');

            // 如果对方有关注自己，才更新对方关注记录中的 followed_at 为 null
            if ($preferenceable->isFollowing($this)) {
                $this->updateFollowedAt($preferenceable, null);
            }

            // 删除关注记录
            return $preference->delete();
        }

        return true;
    }

    /**
     * 切换关注状态
     *
     * @return Preference|bool
     */
    public function toggleFollow(Model $preferenceable)
    {
        return $this->isFollowing($preferenceable) ? $this->unfollow($preferenceable) : $this->follow($preferenceable);
    }

    /**
     * 是否关注了 $preferenceable
     */
    public function isFollowing(Model $preferenceable): bool
    {
        return $this->follows()
            ->withPreferenceable($preferenceable)
            ->snScope($preferenceable->getScopeType(), $preferenceable->getScopeId())
            ->count() > 0;
    }

    /**
     * 是否与 $preferenceable 互关
     */
    public function isMutualFollowed(Model $preferenceable): bool
    {
        return $this->isFollowing($preferenceable) && $preferenceable->isFollowing($this);
    }

    /**
     * 返回我关注的用户列表 仅 UserModel 类型
     */
    public function followingUsers(): MorphToMany
    {
        return $this->morphToMany(
            UserUtils::getUserModel(),           // 目标模型
            'preferenceable',         // 多态关联名
            app(Utils::getPreferenceModel())->getTable(),            // 中间表名
            'preferencer_id',         // 中间表的外键（指向当前模型）
            'preferenceable_id'      // 中间表的另一个外键
        )
            ->wherePivot('preferenceable_type', (new (UserUtils::getUserModel()))->getMorphClass())  // 过滤被关注者类型
            ->wherePivot('type', '=', 'follow')      // 过滤 preference 类类型
            ->withPivot('preferenceable_type', 'options')         // 带上 preference 的其他信息
            ->withTimestamps();               // 时间戳
    }

    /**
     * 返回我关注的用户数量
     */
    public function followingUserCount(): int
    {
        return $this->followingUsers()->count();
    }

    /**
     * 返回我关注的成员列表 仅 MemberModel 类型
     */
    public function followingMembers(): MorphToMany
    {
        return $this->morphToMany(
            MemberUtils::getMemberModel(),           // 目标模型
            'preferenceable',         // 多态关联名
            app(Utils::getPreferenceModel())->getTable(),            // 中间表名
            'preferencer_id',         // 中间表的外键（指向当前模型）
            'preferenceable_id'      // 中间表的另一个外键
        )
            ->wherePivot('preferenceable_type', (new (MemberUtils::getMemberModel()))->getMorphClass())  // 过滤被关注者类型
            ->wherePivot('type', '=', 'follow')      // 过滤 preference 类类型
            ->withPivot('preferenceable_type', 'options')         // 带上 preference 的其他信息
            ->withTimestamps();               // 时间戳
    }

    /**
     * 返回我关注的成员数量
     */
    public function followingMemberCount(): int
    {
        return $this->followingMembers()->count();
    }

    /**
     * 为 $preferenceables 附加关注状态
     *
     * @param  mixed  $preferenceables
     * @return mixed
     */
    public function attachFollowStatus(&$preferenceables, ?callable $resolver = null)
    {
        $follows = $this->follows()->get()->keyBy(function ($item) {
            return \sprintf('%s:%s-%s:%s', $item->preferenceable_type, $item->preferenceable_id, $item->scope_type, $item->scope_id);
        });

        $attachStatus = function ($preferenceable) use ($follows, $resolver) {
            $resolver = $resolver ?? fn ($m) => $m;
            $preferenceable = $resolver($preferenceable);

            if ($preferenceable && \in_array(Followable::class, \class_uses_recursive($preferenceable))) {
                $key = \sprintf('%s:%s-%s:%s', $preferenceable->getMorphClass(), $preferenceable->getKey(), $preferenceable->getScopeType(), $preferenceable->getScopeId());
                $preferenceable->setAttribute('has_followed', $follows->has($key));
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
                return collect($preferenceables->items())->transform($attachStatus);
            case \is_array($preferenceables):
                return \collect($preferenceables)->transform($attachStatus);
            default:
                throw new InvalidArgumentException('Invalid argument type.');
        }
    }

    /**
     * follows 关联
     */
    public function follows(): MorphMany
    {
        return $this->preferences()->withAttributes(['type' => 'follow']);        // withAttributes 如果通过follows 创建 preferences， type 会自动附加到 preferences 中
    }
}
