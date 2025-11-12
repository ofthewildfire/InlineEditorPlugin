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
        // ALL custom field types now use InlineEditableTextColumn for automatic inline editing
        return InlineEditableTextColumn::class;
    }

    public function create(CustomField $customField): Column
    {
        $componentClass = $this->componentMap($customField->type);
        
        // Always create fresh instances to ensure our changes take effect
        $component = $this->container->make($componentClass);

        if (! $component instanceof ColumnInterface) {
            throw new RuntimeException("Component class {$componentClass} must implement ColumnInterface");
        }

        return $component->make($customField)
            ->columnSpan($customField->width->getSpanValue());
    }
}