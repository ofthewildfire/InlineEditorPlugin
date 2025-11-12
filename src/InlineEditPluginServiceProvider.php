<?php

declare(strict_types=1);

namespace OfTheWildfire\FilamentInlineEditColumn;

use Illuminate\Support\ServiceProvider;
use OfTheWildfire\FilamentInlineEditColumn\Filament\Table\Columns\InlineEditableFieldColumnFactory;
use Relaticle\CustomFields\Filament\Tables\Columns\FieldColumnFactory;

final class InlineEditPluginServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge package config so consumers can opt-in to the global override
        $this->mergeConfigFrom(__DIR__ . '/../config/inline-edit-column.php', 'inline-edit-column');

        // Only replace the original FieldColumnFactory when explicitly enabled in config.
        // This avoids forcing a global replacement for all consumers of FieldColumnFactory.
        if (config('inline-edit-column.enabled', false)) {
            // Replace the original FieldColumnFactory with our InlineEditableFieldColumnFactory
            $this->app->bind(FieldColumnFactory::class, InlineEditableFieldColumnFactory::class);
        }
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'inline-edit-column');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/inline-edit-column'),
        ], 'inline-edit-column-views');

        // Publish config so applications can enable the override if desired
        $this->publishes([
            __DIR__ . '/../config/inline-edit-column.php' => config_path('inline-edit-column.php'),
        ], 'inline-edit-column-config');
    }
}