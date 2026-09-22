<?php

namespace Wsmallnews\Preference\Commands;

use Wsmallnews\Support\Commands\PackageInstallCommand;

class PreferenceInstallCommand extends PackageInstallCommand
{
    protected string $packageName = 'sn-preference';
}
