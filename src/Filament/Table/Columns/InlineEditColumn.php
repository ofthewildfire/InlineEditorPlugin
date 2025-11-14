<?php

declare(strict_types=1);

namespace OfTheWildfire\FilamentInlineEditColumn\Filament\Table\Columns;

use Closure;
use Filament\Support\RawJs;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\Concerns;
use Filament\Forms\Components\Concerns\HasStep;
use Filament\Tables\Columns\Contracts\Editable;
use Filament\Forms\Components\Concerns\HasInputMode;
use Filament\Tables\Columns\Concerns\CanFormatState;
use Filament\Forms\Components\Concerns\HasExtraInputAttributes;

class InlineEditColumn extends Column implements Editable
{
    use Concerns\CanBeValidated;
    use Concerns\CanUpdateState;
    use HasExtraInputAttributes;
    use CanFormatState;
    use HasInputMode;
    use HasStep;

    protected string $view = 'inline-edit-column::columns.inline-edit-column';

    protected string | RawJs | Closure | null $mask = null;
    protected string | Closure | null $type = null;
    protected string | Closure | null $inputComponent = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->disabledClick();
    }

    public function type(string | Closure | null $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getType(): string
    {
        return $this->evaluate($this->type) ?? 'text';
    }
    
    public function inputComponent(string | Closure | null $component): static
    {
        $this->inputComponent = $component;
        return $this;
    }
    
    public function getInputComponent(): ?string
    {
        return $this->evaluate($this->inputComponent);
    }
}