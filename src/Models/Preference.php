<?php

namespace Wsmallnews\Preference\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Wsmallnews\Support\Models\SupportModel;
use Wsmallnews\Support\Support\Utils as SupportUtils;

class Preference extends SupportModel
{
    use SoftDeletes;

    protected $table = 'sn_preferences';

    protected $casts = [
        'options' => 'json',
    ];

    public function preferencer(): MorphTo
    {
        return $this->morphTo();
    }

    public function preferenceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeWithType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeWithPreferencer($query, $preferencer)
    {
        return $query->where('preferencer_type', $preferencer->getMorphClass())
            ->where('preferencer_id', $preferencer->id);
    }

    public function scopeWithPreferenceable($query, $preferenceable)
    {
        return $query->where('preferenceable_type', $preferenceable->getMorphClass())
            ->where('preferenceable_id', $preferenceable->id);
    }

    public function scopeWithPreferenceType($query, $preferenceable)
    {
        $preferenceType = is_object($preferenceable) ?
            $preferenceable->getMorphClass() : app($preferenceable)->getMorphClass();

        return $query->where('preferenceable_type', $preferenceType);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(SupportUtils::getTenantModel());
    }
}
