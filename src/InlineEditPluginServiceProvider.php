<?php

declare(strict_types=1);

namespace OfTheWildfire\FilamentInlineEditColumn;

use Illuminate\Support\ServiceProvider;

final class InlineEditPluginServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'inline-edit-column');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/inline-edit-column'),
        ], 'inline-edit-column-views');
    }
}