<?php

declare(strict_types=1);

namespace TemplateVendor\PluginName;

class PluginName
{
    public static function key(): string
    {
        return 'template-vendor/plugin-name';
    }

    public static function provider(): string
    {
        return PluginNameServiceProvider::class;
    }
}
