<?php

declare(strict_types=1);

namespace Drupalens\Project;

/**
 * One entry from composer.lock.
 */
final class Package
{
    public function __construct(
        public readonly string $name,
        public readonly string $version,
        public readonly string $type,
        public readonly bool $abandoned = false,
        public readonly ?string $replacement = null,
    ) {
    }

    /** A package hosted on drupal.org (name "drupal/<project>"). */
    public function isDrupalOrg(): bool
    {
        return str_starts_with($this->name, 'drupal/');
    }

    /** The drupal.org project short name (after "drupal/"), or null. */
    public function project(): ?string
    {
        return $this->isDrupalOrg() ? substr($this->name, strlen('drupal/')) : null;
    }

    public function isCore(): bool
    {
        return $this->type === 'drupal-core';
    }

    public function isModuleOrTheme(): bool
    {
        return in_array($this->type, ['drupal-module', 'drupal-theme'], true);
    }
}
