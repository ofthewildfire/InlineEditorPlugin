# InlineEditColumn Usage Guide

## Quick Start

Replace any `TextColumn` with `InlineEditColumn` for instant click-to-edit functionality.

```php
use OfTheWildfire\FilamentInlineEditColumn\Filament\Table\Columns\InlineEditColumn;

// Before
TextColumn::make('name'),

// After
InlineEditColumn::make('name'),
```

## How it works

- **Click** any cell to start editing
- **Enter** to save your changes
- **Escape** or **✖** to cancel

## Examples

```php
public static function table(Table $table): Table
{
    return $table->columns([
        // Basic text editing
        InlineEditColumn::make('name'),

        // Email field
        InlineEditColumn::make('email')->type('email'),

        // Numbers
        InlineEditColumn::make('price')->type('number'),

        // With all the usual Filament options
        InlineEditColumn::make('title')
            ->searchable()
            ->sortable()
            ->toggleable(),
    ]);
}
```

## Input Types

Set the input type with `->type()`:

- `text` (default)
- `email`
- `number`
- `url`
- `tel`
- `password`

## That's it!

Works with all standard Filament column features like sorting, searching, and toggling. The data saves automatically to your database when you press Enter.
