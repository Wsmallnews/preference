<?php

namespace Wsmallnews\Preference\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Wsmallnews\Preference\Models\Preference;

trait Preferencer
{
    public function preferences(): MorphMany
    {
        return $this->morphMany(Preference::class, 'preferencer');
    }
}
