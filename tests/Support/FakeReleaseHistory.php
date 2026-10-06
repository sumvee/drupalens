<?php

declare(strict_types=1);

namespace Drupalens\Tests\Support;

use Drupalens\Data\ProjectReleases;
use Drupalens\Data\ReleaseHistory;

/**
 * Array-backed ReleaseHistory for deterministic, offline check tests.
 */
final class FakeReleaseHistory implements ReleaseHistory
{
    /**
     * @param array<string,ProjectReleases> $map project short name -> releases
     */
    public function __construct(private readonly array $map = [])
    {
    }

    public function fetch(string $project): ?ProjectReleases
    {
        return $this->map[$project] ?? null;
    }
}
