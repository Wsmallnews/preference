<?php

use Wsmallnews\Preference\Models;

return [
    /**
     * Default scopeable
     */
    'scopeable' => [
        'scope_type' => 'sn-preference',
        'scope_id' => 0,
    ],

    /**
     * Custom models
     */
    'models' => [
        'preference' => Models\Preference::class,
    ],

    /**
     * 文件基础目录，会自动拼接当前年月日 (仅用于 filament 默认上传组件 (Forms\Components\FileUpload))
     */
    'file_directory' => 'sn/preference/',
];
