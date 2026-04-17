<?php

namespace Wsmallnews\Preference\Traits;

use Illuminate\Database\Eloquent\Builder;
use Wsmallnews\Preference\Models\Preference;

/**
 * @method static \Illuminate\Database\Eloquent\Builder wherePreferenceType(string $type)
 * @method static \Illuminate\Database\Eloquent\Builder wherePreferencer($preferencer)
 */
trait HasPreferenceable
{
    public function preferences()
    {
        return $this->morphMany(Preference::class, 'preferenceable');
    }

    public function preferencesByType(string $type)
    {
        return $this->preferences()->where('type', $type);
    }

    public function preferenceCount(string $type)
    {
        return $this->preferencesByType($type)->count();
    }

    public function hasBeenLikedBy($preferencer)
    {
        return $this->hasBeenPreferredBy('like', $preferencer);
    }

    public function hasBeenFollowedBy($preferencer)
    {
        return $this->hasBeenPreferredBy('follow', $preferencer);
    }

    public function hasBeenSubscribedBy($preferencer)
    {
        return $this->hasBeenPreferredBy('subscribe', $preferencer);
    }

    public function hasBeenPreferredBy(string $type, $preferencer)
    {
        return $this->preferences()
            ->where('type', $type)
            ->where('preferencer_type', get_class($preferencer))
            ->where('preferencer_id', $preferencer->id)
            ->exists();
    }

    public function preferencersByType(string $type, array $options = [])
    {
        $query = $this->preferences()
            ->where('type', $type)
            ->with('preferencer');

        if (isset($options['limit'])) {
            $query->limit($options['limit']);
        }

        if (isset($options['order_by'])) {
            $query->orderBy($options['order_by'], $options['order_dir'] ?? 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query->get()->pluck('preferencer');
    }

    public function scopeWherePreferenceType(Builder $query, string $type)
    {
        return $query->whereHas('preferences', function (Builder $q) use ($type) {
            $q->where('type', $type);
        });
    }

    public function scopeWherePreferencer(Builder $query, $preferencer)
    {
        return $query->whereHas('preferences', function (Builder $q) use ($preferencer) {
            $q->where('preferencer_type', get_class($preferencer))
                ->where('preferencer_id', $preferencer->id);
        });
    }
}
