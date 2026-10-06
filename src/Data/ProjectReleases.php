<?php

declare(strict_types=1);

namespace Drupalens\Data;

/**
 * The release-history feed for one drupal.org project.
 */
final class ProjectReleases
{
    /**
     * @param string[]  $supportedBranches branch prefixes, e.g. ["8.x-1.", "2."]
     * @param Release[] $releases           newest first, as drupal.org returns them
     */
    public function __construct(
        public readonly string $shortName,
        public readonly string $projectStatus,
        public readonly array $supportedBranches,
        public readonly array $releases,
    ) {
    }

    /** Whether the project itself is still supported (not unsupported/obsolete). */
    public function isSupported(): bool
    {
        return $this->projectStatus === 'published';
    }

    /** The newest published release, or null. */
    public function latest(): ?Release
    {
        foreach ($this->releases as $r) {
            if ($r->isPublished()) {
                return $r;
            }
        }
        return null;
    }

    /** The newest published security release, or null. */
    public function latestSecurity(): ?Release
    {
        foreach ($this->releases as $r) {
            if ($r->isPublished() && $r->security) {
                return $r;
            }
        }
        return null;
    }

    /** Whether a version string sits on one of the supported branches. */
    public function isOnSupportedBranch(string $version): bool
    {
        foreach ($this->supportedBranches as $branch) {
            if ($branch !== '' && str_starts_with($version, $branch)) {
                return true;
            }
        }
        return false;
    }
}
