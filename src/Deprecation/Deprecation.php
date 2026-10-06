<?php

declare(strict_types=1);

namespace Drupalens\Deprecation;

/**
 * One deprecated Drupal API symbol and how to replace it.
 */
final class Deprecation
{
    public function __construct(
        public readonly string $symbol,
        public readonly string $kind,        // function | constant | staticmethod
        public readonly string $deprecatedIn,
        public readonly string $removedIn,
        public readonly string $replacement,
    ) {
    }

    /** Major version in which the symbol is removed (e.g. "9" from "9.0.0"). */
    public function removedInMajor(): int
    {
        return (int) $this->removedIn;
    }
}
