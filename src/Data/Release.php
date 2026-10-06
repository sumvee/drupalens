<?php

declare(strict_types=1);

namespace Drupalens\Data;

/**
 * One release from a drupal.org release-history feed.
 */
final class Release
{
    public function __construct(
        public readonly string $version,
        public readonly string $status,
        public readonly bool $security,
        public readonly ?int $date = null,
        public readonly ?string $coreCompatibility = null,
    ) {
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }
}
