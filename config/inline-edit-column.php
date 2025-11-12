<?php

declare(strict_types=1);

// Package config for filament-inline-edit-column
// Consumers must explicitly enable the global override to replace the
// original FieldColumnFactory with the inline-editing factory.

return [
    // When true the package will replace Relaticle's FieldColumnFactory
    // with the InlineEditableFieldColumnFactory globally. Defaults to false
    // so the original factory remains available unless the app opts in.
    'enabled' => true,
];
