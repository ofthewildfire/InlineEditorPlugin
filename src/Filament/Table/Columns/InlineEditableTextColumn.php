<?php

declare(strict_types=1);

namespace OfTheWildfire\FilamentInlineEditColumn\Filament\Table\Columns;

use Filament\Tables\Columns\Column as BaseColumn;
use Illuminate\Database\Eloquent\Builder;
use Relaticle\CustomFields\Models\CustomField;
use Relaticle\CustomFields\Queries\ColumnSearchableQuery;
use Relaticle\CustomFields\Filament\Tables\Columns\ColumnInterface;

final readonly class InlineEditableTextColumn implements ColumnInterface
{
    public function make(CustomField $customField): BaseColumn
    {
        return InlineEditColumn::make("custom_fields.$customField->code")
            ->label($customField->name)
            ->sortable(
                condition: ! $customField->settings->encrypted,
                query: function (Builder $query, string $direction) use ($customField): Builder {
                    $table = $query->getModel()->getTable();
                    $key = $query->getModel()->getKeyName();

                    return $query->orderBy(
                        $customField->values()
                            ->select($customField->getValueColumn())
                            ->whereColumn('custom_field_values.entity_id', "$table.$key")
                            ->limit(1),
                        $direction
                    );
                }
            )
            ->searchable(
                condition: $customField->settings->searchable,
                query: fn (Builder $query, string $search) => (new ColumnSearchableQuery)->builder($query, $customField, $search),
            )
            ->getStateUsing(fn ($record) => $record->getCustomFieldValue($customField))
            ->updateStateUsing(function ($record, $state) use ($customField) {
                $record->setCustomFieldValue($customField->code, $state);
                return $state;
            })
            ->type(function () use ($customField) {
                return match ($customField->type->value) {
                    'number' => 'number',
                    'link' => 'url',
                    'currency' => 'number',
                    default => 'text',
                };
            });
    }
}