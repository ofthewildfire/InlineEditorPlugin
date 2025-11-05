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
            CustomFieldType::TEXT, 
            CustomFieldType::TEXTAREA, 
            CustomFieldType::NUMBER, 
            CustomFieldType::LINK, 
            CustomFieldType::CURRENCY => InlineEditableTextColumn::class,
            
            CustomFieldType::SELECT, 
            CustomFieldType::RADIO => \Relaticle\CustomFields\Filament\Tables\Columns\SingleValueColumn::class,
            CustomFieldType::COLOR_PICKER => \Relaticle\CustomFields\Filament\Tables\Columns\ColorColumn::class,
            CustomFieldType::MULTI_SELECT, 
            CustomFieldType::TOGGLE_BUTTONS, 
            CustomFieldType::CHECKBOX_LIST,
            CustomFieldType::TAGS_INPUT => \Relaticle\CustomFields\Filament\Tables\Columns\MultiValueColumn::class,
            CustomFieldType::CHECKBOX, 
            CustomFieldType::TOGGLE => \Relaticle\CustomFields\Filament\Tables\Columns\IconColumn::class,
            CustomFieldType::DATE, 
            CustomFieldType::DATE_TIME => \Relaticle\CustomFields\Filament\Tables\Columns\DateTimeColumn::class,
            CustomFieldType::RICH_EDITOR,
            CustomFieldType::MARKDOWN_EDITOR => InlineEditableTextColumn::class,
            
            default => InlineEditableTextColumn::class,
        };
    }

    public function create(CustomField $customField): Column
    {
        $componentClass = $this->componentMap($customField->type);

        if (! isset($this->instanceCache[$componentClass])) {
            $component = $this->container->make($componentClass);

            if (! $component instanceof ColumnInterface) {
                throw new RuntimeException("Component class {$componentClass} must implement ColumnInterface");
            }

            $this->instanceCache[$componentClass] = $component;
        } else {
            $component = $this->instanceCache[$componentClass];
        }

        return $component->make($customField)
            ->columnSpan($customField->width->getSpanValue());
    }
}