<?php

declare(strict_types=1);

namespace Drupalens\Data;

/**
 * Source of drupal.org release-history data for a project. Implementations
 * may hit the network, read a cache, or be faked in tests.
 */
interface ReleaseHistory
{
    /**
     * @return ProjectReleases|null null when the project is unknown or
     *                              cannot be retrieved
     */
    public function fetch(string $project): ?ProjectReleases;
}
