<?php

namespace Wsmallnews\Preference\Traits;

use Wsmallnews\Preference\Preference;

trait HasPreferences
{
    public function preferences()
    {
        return Preference::for($this);
    }

    public function like($target)
    {
        return $this->preferences()->toggle('like', $target);
    }

    public function unlike($target)
    {
        return $this->preferences()->remove('like', $target);
    }

    public function hasLiked($target)
    {
        return $this->preferences()->exists('like', $target);
    }

    public function follow($target)
    {
        return $this->preferences()->toggle('follow', $target);
    }

    public function unfollow($target)
    {
        return $this->preferences()->remove('follow', $target);
    }

    public function hasFollowed($target)
    {
        return $this->preferences()->exists('follow', $target);
    }

    public function subscribe($target)
    {
        return $this->preferences()->toggle('subscribe', $target);
    }

    public function unsubscribe($target)
    {
        return $this->preferences()->remove('subscribe', $target);
    }

    public function hasSubscribed($target)
    {
        return $this->preferences()->exists('subscribe', $target);
    }

    public function favorite($target)
    {
        return $this->preferences()->toggle('favorite', $target);
    }

    public function unfavorite($target)
    {
        return $this->preferences()->remove('favorite', $target);
    }

    public function hasFavorited($target)
    {
        return $this->preferences()->exists('favorite', $target);
    }

    public function view($target, array $options = [])
    {
        return $this->preferences()->add('view', $target, $options);
    }

    public function preferenceCount(string $type, $target = null)
    {
        return $this->preferences()->count($type, $target);
    }

    public function preferencesByType(string $type, array $options = [])
    {
        return $this->preferences()->get($type, $options);
    }
}
