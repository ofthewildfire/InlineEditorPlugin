<?php

declare(strict_types=1);

namespace OfTheWildfire\FilamentInlineEditColumn\Filament\Table\Columns;

use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Database\Eloquent\Model;
use Relaticle\CustomFields\Models\CustomField;
use Relaticle\CustomFields\Support\Utils;

final readonly class CustomFieldsColumnOverride
{
    public static function all(Model $instance): array
    {
        if (Utils::isTableColumnsEnabled() === false) {
            return [];
        }

        // Use OUR factory instead of the original
        $fieldColumnFactory = new InlineEditableFieldColumnFactory(app());

        return $instance->customFields()
            ->visibleInList()
            ->with('options')
            ->get()
            ->map(fn (CustomField $customField) => $fieldColumnFactory->create($customField)
                ->toggleable(
                    condition: Utils::isTableColumnsToggleableEnabled(),
                    isToggledHiddenByDefault: $customField->settings->list_toggleable_hidden
                )
            )
            ->toArray();
    }

    public static function forRelationManager(RelationManager $relationManager): array
    {
        return self::all($relationManager->getRelationship()->getModel());
    }
}