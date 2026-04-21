<?php

namespace Wsmallnews\Preference;

use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Relations\Relation;
use Livewire\Features\SupportTesting\Testable;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Wsmallnews\Preference\Commands\PreferenceCommand;
use Wsmallnews\Preference\Support\Utils;

class PreferenceServiceProvider extends PackageServiceProvider
{
    public static string $name = 'sn-preference';

    public static string $viewNamespace = 'sn-preference';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasConfigFile()
            ->hasTranslations()
            ->hasViews(static::$viewNamespace)
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('wsmallnews/preference');
            });

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
            $package->runsMigrations();
        }
    }

    public function packageRegistered(): void {}

    public function packageBooted(): void
    {
        // 注册模型别名
        Relation::enforceMorphMap([
            'sn-preference' => Utils::getPreferenceModel(),
        ]);

        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        FilamentAsset::registerScriptData(
            $this->getScriptData(),
            $this->getAssetPackageName()
        );

        // Icon Registration
        FilamentIcon::register($this->getIcons());

        // Handle Stubs
        if (app()->runningInConsole()) {
            foreach (app(Filesystem::class)->files(__DIR__ . '/../stubs/') as $file) {
                $this->publishes([
                    $file->getRealPath() => base_path("stubs/preference/{$file->getFilename()}"),
                ], 'preference-stubs');
            }
        }
    }

    protected function getAssetPackageName(): ?string
    {
        return 'wsmallnews/preference';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            // AlpineComponent::make('preference', __DIR__ . '/../resources/dist/components/preference.js'),
            // Css::make('preference-styles', __DIR__ . '/../resources/dist/preference.css'),
            // Js::make('preference-scripts', __DIR__ . '/../resources/dist/preference.js'),
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            PreferenceCommand::class,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getRoutes(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getScriptData(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            '2026_04_15_162434_create_sn_preferences_table',
        ];
    }
}
