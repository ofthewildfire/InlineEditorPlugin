<?php

declare(strict_types=1);

namespace OfTheWildfire\FilamentInlineEditColumn\Traits;

use OfTheWildfire\FilamentInlineEditColumn\Filament\Table\Columns\InlineEditableCustomFieldsColumn;
use OfTheWildfire\FilamentInlineEditColumn\Filament\Table\Columns\InlineEditColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;

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
    
    public static function autoDiscoverAllInlineEditableColumns(Model $instance, array $includeFields = [], array $excludeFields = []): array
    {
        $columns = [];
        
        // Get the table name from the model instance
        $tableName = $instance->getTable();
        $fillableFields = $instance->getFillable();
        $hiddenFields = $instance->getHidden();
        $defaultSkipFields = [
            'id', 'password', 'remember_token', 'email_verified_at', 'deleted_at',
            'team_id', 'creator_id', 'account_owner_id', 'user_id', 'created_by', 'updated_by'
        ];
        
        // Merge default skip fields with user-provided exclude fields
        $skipFields = array_merge($defaultSkipFields, $excludeFields);
        
        // Get all columns from the database table
        $tableColumns = Schema::getColumnListing($tableName);
        
        // If includeFields is specified, only include those fields
        if (!empty($includeFields)) {
            $tableColumns = array_intersect($tableColumns, $includeFields);
        }
        
        // Auto-discover regular model fields and make them ALL inline editable
        foreach ($tableColumns as $columnName) {
            // Skip system fields and hidden fields
            if (in_array($columnName, $skipFields) || in_array($columnName, $hiddenFields)) {
                continue;
            }
            
            // Make EVERYTHING an inline editable column with appropriate input types
            $column = InlineEditColumn::make($columnName)
                ->label(ucwords(str_replace('_', ' ', $columnName)))
                ->searchable()
                ->sortable()
                ->toggleable()  // Make columns toggleable so they appear in the columns dropdown
                ->updateStateUsing(function ($record, $state) use ($columnName) {
                    $record->update([$columnName => $state]);
                    return $state;
                });
            
            // Set appropriate input type based on field characteristics
            if (str_contains($columnName, 'email')) {
                $column->type('email');
            } elseif (str_contains($columnName, 'url') || str_contains($columnName, 'link')) {
                $column->type('url');
            } elseif (str_contains($columnName, 'phone')) {
                $column->type('tel');
            } elseif (str_contains($columnName, 'password')) {
                $column->type('password');
            } elseif (str_contains($columnName, 'number') || str_contains($columnName, 'count') || str_contains($columnName, 'amount')) {
                $column->type('number');
            } elseif (str_contains($columnName, 'date') || str_contains($columnName, 'at')) {
                $column->type('datetime-local');
            } else {
                $column->type('text');
            }
            
            $columns[] = $column;
        }
        
        // Add all custom fields as inline editable (these come from the fixed factory)
        $customFieldColumns = static::getInlineEditableCustomFieldColumns($instance);
        
        return array_merge($columns, $customFieldColumns);
    }
}