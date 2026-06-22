<?php

namespace Wsmallnews\Preference;

use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Filesystem\Filesystem;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Wsmallnews\Preference\Commands\PreferenceInstallCommand;
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
            ->hasMigrations($this->getMigrations())
            ->hasTranslations()
            ->hasViews(static::$viewNamespace);
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

        // 注册 livewire 命名空间（自动发现 src/Livewire/ 下的组件）
        Livewire::addNamespace(
            namespace: 'sn-preference',
            classNamespace: 'Wsmallnews\\Preference\\Livewire'
        );
        // 注册 Filament 命名空间下 preference 组件（自动发现 src/Filament/Pages/Preference/Components/ 下的组件）
        Livewire::addNamespace(
            namespace: 'sn-preference-fi-preference-components',
            classNamespace: 'Wsmallnews\\Preference\\Filament\\Pages\\Preference\\Components'
        );
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
            PreferenceInstallCommand::class,
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
            'create_sn_preferences_table',
        ];
    }
}
