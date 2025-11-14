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
    
    public static function getUniqueInlineEditableColumns(Model $instance, array $includeFields = [], array $excludeFields = []): array
    {
        // Get all columns including any that might be automatically added
        $allColumns = static::autoDiscoverAllInlineEditableColumns($instance, $includeFields, $excludeFields);
        
        // Remove duplicates by name - keep only the first occurrence
        $uniqueColumns = [];
        $seenNames = [];
        
        foreach ($allColumns as $column) {
            $columnName = $column->getName();
            if (!in_array($columnName, $seenNames)) {
                $uniqueColumns[] = $column;
                $seenNames[] = $columnName;
            }
        }
        
        return $uniqueColumns;
    }
    
    public static function getOnlyInlineEditableColumns(Model $instance, array $includeFields = [], array $excludeFields = []): array
    {
        $columns = [];
        $tableName = $instance->getTable();
        $defaultSkipFields = [
            'id', 'password', 'remember_token', 'email_verified_at', 'deleted_at',
            'team_id', 'creator_id', 'account_owner_id', 'user_id', 'created_by', 'updated_by'
        ];
        $skipFields = array_merge($defaultSkipFields, $excludeFields);
        $tableColumns = \Illuminate\Support\Facades\Schema::getColumnListing($tableName);
        
        if (!empty($includeFields)) {
            $tableColumns = array_intersect($tableColumns, $includeFields);
        }
        
        // Add regular database columns
        foreach ($tableColumns as $columnName) {
            if (in_array($columnName, $skipFields)) {
                continue;
            }
            
            $column = InlineEditColumn::make($columnName)
                ->label(ucwords(str_replace('_', ' ', $columnName)))
                ->searchable()
                ->sortable()
                ->toggleable()
                ->updateStateUsing(function ($record, $state) use ($columnName) {
                    $record->update([$columnName => $state]);
                    return $state;
                });
            
            // Set input types
            if (str_contains($columnName, 'email')) {
                $column->type('email');
            } elseif (str_contains($columnName, 'url') || str_contains($columnName, 'link')) {
                $column->type('url');
            } elseif (str_contains($columnName, 'phone')) {
                $column->type('tel');
            } elseif (str_contains($columnName, 'number') || str_contains($columnName, 'count') || str_contains($columnName, 'amount')) {
                $column->type('number');
            } elseif (str_contains($columnName, 'date') || str_contains($columnName, 'at')) {
                $column->type('datetime-local');
            } else {
                $column->type('text');
            }
            
            $columns[] = $column;
        }
        
        // Add ONLY our custom field columns - bypass the automatic system entirely
        $customFieldColumns = static::getInlineEditableCustomFieldColumns($instance);
        $columns = array_merge($columns, $customFieldColumns);
        
        return $columns;
    }
    
    public static function addInlineEditableCustomFields(array $columns, Model $instance): array
    {
        return array_merge($columns, static::getInlineEditableCustomFieldColumns($instance));
    }
    
    public static function autoDiscoverAllInlineEditableColumns(Model $instance, array $includeFields = [], array $excludeFields = []): array
    {
        $columns = [];
        $columnNames = []; // Track column names to prevent duplicates
        
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
            } elseif (str_contains($columnName, 'date') && str_contains($columnName, 'time')) {
                $column->type('datetime-local');
            } elseif (str_contains($columnName, 'date') || $columnName === 'created_at' || $columnName === 'updated_at') {
                $column->type('datetime-local');
            } else {
                $column->type('text');
            }
            
            $columns[] = $column;
            $columnNames[] = $columnName;
        }
        
        // Add custom fields using our inline editable factory
        $customFieldColumns = static::getInlineEditableCustomFieldColumns($instance);
        
        // Only add custom field columns that don't already exist
        foreach ($customFieldColumns as $customColumn) {
            $customColumnName = $customColumn->getName();
            if (!in_array($customColumnName, $columnNames)) {
                $columns[] = $customColumn;
                $columnNames[] = $customColumnName;
            }
        }
        
        return $columns;
    }
}