<?php

declare(strict_types=1);

namespace OfTheWildfire\FilamentInlineEditColumn\Traits;

use Exception;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Builder;
use OfTheWildfire\FilamentInlineEditColumn\Filament\Table\Columns\CustomFieldsColumnOverride;
use Relaticle\CustomFields\Filament\Tables\Filter\CustomFieldsFilter;

trait InteractsWithInlineEditableCustomFields
{
    /**
     * @throws BindingResolutionException
     */
    public function table(Table $table): Table
    {
        $model = $this instanceof RelationManager 
            ? $this->getRelationship()->getModel()::class 
            : $this->getModel();
        
        $instance = app($model);

        try {
            $table = static::getResource()::table($table);
        } catch (Exception $exception) {
            $table = parent::table($table);
        }

        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $query->with('customFieldValues.customField');
            })
            // Use OUR override instead of the original CustomFieldsColumn
            ->pushColumns(CustomFieldsColumnOverride::all($instance))
            ->pushFilters(CustomFieldsFilter::all($instance));
    }
}