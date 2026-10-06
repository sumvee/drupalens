<?php

declare(strict_types=1);

namespace Drupalens\Version;

/**
 * Normalizes the two version dialects drupalens must compare:
 *   - composer.lock semantic: "1.9.0", "6.3.1", "v6.4.1"
 *   - drupal.org legacy:      "8.x-1.17"  (core.x-MAJOR.MINOR)
 * to a comparable "X.Y.Z" so composer-locked installs can be compared to
 * release-history versions. Returns null when the version is a dev/unstable
 * string that cannot be safely ordered.
 */
final class VersionNormalizer
{
    public function normalize(string $version): ?string
    {
        $v = ltrim(trim($version), 'vV');
        if ($v === '') {
            return null;
        }

        // Legacy "8.x-1.17" (optionally with a prerelease suffix).
        if (preg_match('/^\d+\.x-(\d+)\.(\d+)(?:-.*)?$/', $v, $m)) {
            return "{$m[1]}.{$m[2]}.0";
        }
        // Legacy dev / unparseable legacy ("8.x-1.x-dev").
        if (str_contains($v, '.x-')) {
            return null;
        }
        // Semantic "1.2.3" (optionally with a prerelease suffix).
        if (preg_match('/^(\d+)\.(\d+)\.(\d+)(?:-.*)?$/', $v, $m)) {
            return "{$m[1]}.{$m[2]}.{$m[3]}";
        }
        // Major.minor only ("1.17" -> "1.17.0").
        if (preg_match('/^(\d+)\.(\d+)$/', $v, $m)) {
            return "{$m[1]}.{$m[2]}.0";
        }
        return null;
    }

    /** True when $candidate is a strictly newer, comparable version than $installed. */
    public function isNewer(string $candidate, string $installed): bool
    {
        $c = $this->normalize($candidate);
        $i = $this->normalize($installed);
        if ($c === null || $i === null) {
            return false;
        }
        return version_compare($c, $i, '>');
    }
}
