<?php

declare(strict_types=1);

namespace Drupalens\Project;

/**
 * The static model of a Drupal project, built from composer.lock.
 */
final class Project
{
    /**
     * @param Package[] $packages
     */
    public function __construct(
        public readonly string $path,
        public readonly ?string $coreVersion,
        public readonly array $packages,
    ) {
    }

    /**
     * Contrib modules and themes hosted on drupal.org (candidates for the
     * release-history / security checks).
     *
     * @return Package[]
     */
    public function drupalProjects(): array
    {
        return array_values(array_filter(
            $this->packages,
            static fn (Package $p): bool => $p->isDrupalOrg() && $p->isModuleOrTheme(),
        ));
    }

    /**
     * Non-drupal.org packages (candidates for Packagist advisory checks).
     *
     * @return Package[]
     */
    public function nonDrupalPackages(): array
    {
        return array_values(array_filter(
            $this->packages,
            static fn (Package $p): bool => !$p->isDrupalOrg(),
        ));
    }
}
