<?php

declare(strict_types=1);

namespace OfTheWildfire\FilamentInlineEditColumn\Filament\Table\Columns;

use Filament\Tables\Columns\Column;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Relaticle\CustomFields\Enums\CustomFieldType;
use Relaticle\CustomFields\Models\CustomField;
use Relaticle\CustomFields\Filament\Tables\Columns\ColumnInterface;
use RuntimeException;

final class InlineEditableFieldColumnFactory
{
    private array $instanceCache = [];

    public function __construct(private readonly Container $container) {}

    private function componentMap(CustomFieldType $type): string
    {
        return match ($type) {
            // Make most fields inline editable!
            CustomFieldType::TEXT, 
            CustomFieldType::TEXTAREA, 
            CustomFieldType::NUMBER, 
            CustomFieldType::LINK, 
            CustomFieldType::CURRENCY,
            CustomFieldType::RICH_EDITOR,
            CustomFieldType::MARKDOWN_EDITOR,
            CustomFieldType::SELECT, 
            CustomFieldType::RADIO,
            CustomFieldType::DATE, 
            CustomFieldType::DATE_TIME => InlineEditableTextColumn::class,
            
            // Keep boolean/checkbox fields as regular display columns (show checkmarks, not editable)
            CustomFieldType::CHECKBOX, 
            CustomFieldType::TOGGLE => \Relaticle\CustomFields\Filament\Tables\Columns\IconColumn::class,
            
            // Other complex field types - use original columns for proper display
            CustomFieldType::COLOR_PICKER => \Relaticle\CustomFields\Filament\Tables\Columns\ColorColumn::class,
            CustomFieldType::MULTI_SELECT, 
            CustomFieldType::TOGGLE_BUTTONS, 
            CustomFieldType::CHECKBOX_LIST,
            CustomFieldType::TAGS_INPUT => \Relaticle\CustomFields\Filament\Tables\Columns\MultiValueColumn::class,
            
            default => InlineEditableTextColumn::class,
        };
    }

    public function create(CustomField $customField): Column
    {
        $componentClass = $this->componentMap($customField->type);
        
        // Create fresh instances
        $component = $this->container->make($componentClass);

        if (! $component instanceof ColumnInterface) {
            throw new RuntimeException("Component class {$componentClass} must implement ColumnInterface");
        }

        return $component->make($customField)
            ->columnSpan($customField->width->getSpanValue());
    }
}