<?php

namespace Wsmallnews\Preference\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Wsmallnews\Preference\Preference
 */
class Preference extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Wsmallnews\Preference\Preference::class;
    }
}
