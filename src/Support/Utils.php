<?php

declare(strict_types=1);

namespace Wsmallnews\Preference\Support;

use Wsmallnews\Preference\Exceptions\PreferenceException;
use Wsmallnews\Support\Data\ScopeableContext;
use Wsmallnews\Support\Exceptions\InvalidScopeException;
use Wsmallnews\Support\Support\Utils as SupportUtils;

/**
 * Utility class for Preference package configuration and helpers.
 */
class Utils
{
    /**
     * Get configuration value.
     *
     * @param  string|null  $name  Configuration key (dot notation)
     * @param  mixed  $default  Default value if not found
     */
    public static function getConfig(?string $name = null, mixed $default = null): mixed
    {
        $config = config('sn-preference');

        return $name ? (data_get($config, $name) ?? $default) : $config;
    }

    /**
     * Get scopeable configuration as ScopeableContext object.
     *
     *
     * @throws PreferenceException
     */
    public static function getScopeableContext(): ScopeableContext
    {
        try {
            return SupportUtils::getScopeFromConfig('sn-preference.scopeable');
        } catch (InvalidScopeException $e) {
            throw new PreferenceException('Scopeable configuration error. ' . $e->getMessage());
        }
    }

    /**
     * Get scopeable array (legacy method for backward compatibility).
     *
     * @return array{scope_type: string, scope_id: int}
     *
     * @throws PreferenceException
     */
    public static function getScopeable(): array
    {
        return self::getScopeableContext()->toArray();
    }

    /**
     * Get scope type.
     *
     *
     * @throws PreferenceException
     */
    public static function getScopeType(): string
    {
        return self::getScopeableContext()->scopeType;
    }

    /**
     * Get scope ID.
     *
     *
     * @throws PreferenceException
     */
    public static function getScopeId(): int
    {
        return self::getScopeableContext()->scopeId;
    }

    /**
     * Get model class by name.
     *
     * @param  string  $name  Model name (e.g., 'post', 'navigation')
     * @param  bool  $shouldException  Whether to throw exception if not found
     *
     * @throws PreferenceException
     */
    public static function getModel(string $name, bool $shouldException = true): ?string
    {
        $model = self::getConfig('models')[$name] ?? null;

        if (blank($model) && $shouldException) {
            throw new PreferenceException("Model {$name} not found.");
        }

        return $model;
    }

    /**
     * Get Comment model class.
     *
     * @return string Models\Comment
     */
    public static function getPreferenceModel(): string
    {
        return self::getModel('preference');
    }

    /**
     * Get file directory path with optional type and date.
     *
     * @param  string|null  $type  Directory type
     */
    public static function getFileDirectory(?string $type = null): string
    {
        return self::getConfig('file_directory', 'sn/preference/') . ($type ? $type . '/' : '') . date('Ymd');
    }
}
