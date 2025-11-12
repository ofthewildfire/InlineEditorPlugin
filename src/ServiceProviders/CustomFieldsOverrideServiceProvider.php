<?php

declare(strict_types=1);

namespace OfTheWildfire\FilamentInlineEditColumn\ServiceProviders;

use Illuminate\Support\ServiceProvider;
use OfTheWildfire\FilamentInlineEditColumn\Filament\Table\Columns\InlineEditableFieldColumnFactory;
use Relaticle\CustomFields\Filament\Tables\Columns\FieldColumnFactory;

class CustomFieldsOverrideServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Only replace the original FieldColumnFactory when explicitly enabled in config
        if (config('inline-edit-column.enabled', false)) {
            $this->app->bind(FieldColumnFactory::class, InlineEditableFieldColumnFactory::class);
        }
    }
}