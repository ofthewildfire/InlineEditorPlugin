<?php

declare(strict_types=1);

namespace OfTheWildfire\FilamentInlineEditColumn\Traits;

use OfTheWildfire\FilamentInlineEditColumn\Filament\Table\Columns\InlineEditableCustomFieldsColumn;
use Illuminate\Database\Eloquent\Model;

trait HasInlineEditableCustomFields
{
    public static function getInlineEditableCustomFieldColumns(Model $instance): array
    {
        return InlineEditableCustomFieldsColumn::all($instance);
    }
    
    public static function addInlineEditableCustomFields(array $columns, Model $instance): array
    {
        return array_merge($columns, static::getInlineEditableCustomFieldColumns($instance));
    }
}