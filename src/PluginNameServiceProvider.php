<?php

declare(strict_types=1);

namespace TemplateVendor\PluginName;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class PluginNameServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $base = dirname(__DIR__);

        $this->loadMigrationsFrom($base.'/database/migrations');
        $this->loadViewsFrom($base.'/resources/views', 'template-vendor-plugin-name');
        $this->loadRoutesFrom($base.'/routes/web.php');

        Blade::componentNamespace(
            'TemplateVendor\\PluginName\\View\\Components',
            'template-vendor-plugin-name'
        );
    }
}
