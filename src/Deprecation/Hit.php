<?php

declare(strict_types=1);

namespace Drupalens\Deprecation;

/**
 * A deprecated symbol found at a line in a source file.
 */
final class Hit
{
    public function __construct(
        public readonly Deprecation $deprecation,
        public readonly int $line,
    ) {
    }
}
