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
        // Replace the original FieldColumnFactory with our InlineEditableFieldColumnFactory
        // This makes ALL custom fields use inline editable columns globally!
        $this->app->bind(FieldColumnFactory::class, InlineEditableFieldColumnFactory::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'inline-edit-column');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/inline-edit-column'),
        ], 'inline-edit-column-views');
    }
}