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
        $customFieldColumns = static::getInlineEditableCustomFieldColumns($instance);
        
        // Debug: Add marker to first column to show this is working
        if (!empty($columns)) {
            $firstColumn = array_shift($columns);
            if (method_exists($firstColumn, 'label')) {
                $firstColumn->label('[PLUGIN ACTIVE] ' . ($firstColumn->getLabel() ?? 'No Label'));
            }
            array_unshift($columns, $firstColumn);
        }
        
        return array_merge($columns, $customFieldColumns);
    }
}