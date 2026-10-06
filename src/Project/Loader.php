<?php

declare(strict_types=1);

namespace Drupalens\Project;

use RuntimeException;

/**
 * Builds a Project from a Drupal codebase by reading composer.lock.
 * No Drupal bootstrap and no database: static analysis only.
 */
final class Loader
{
    public function load(string $path): Project
    {
        $lockFile = rtrim($path, '/') . '/composer.lock';
        if (!is_file($lockFile)) {
            throw new RuntimeException("no composer.lock found at {$path} (is this a Composer-managed Drupal project?)");
        }

        $raw = file_get_contents($lockFile);
        if ($raw === false) {
            throw new RuntimeException("cannot read {$lockFile}");
        }

        /** @var array{packages?: list<array<string,mixed>>, packages-dev?: list<array<string,mixed>>} $data */
        $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

        $entries = array_merge($data['packages'] ?? [], $data['packages-dev'] ?? []);
        $packages = array_map([$this, 'toPackage'], $entries);

        $coreVersion = null;
        foreach ($packages as $pkg) {
            if ($pkg->isCore()) {
                $coreVersion = $pkg->version;
                break;
            }
        }

        return new Project($path, $coreVersion, $packages);
    }

    /**
     * @param array<string,mixed> $entry
     */
    private function toPackage(array $entry): Package
    {
        // composer "abandoned" is false, true, or a replacement package name.
        $abandoned = $entry['abandoned'] ?? false;

        return new Package(
            name: (string) ($entry['name'] ?? ''),
            version: (string) ($entry['version'] ?? ''),
            type: (string) ($entry['type'] ?? 'library'),
            abandoned: $abandoned !== false,
            replacement: is_string($abandoned) ? $abandoned : null,
        );
    }
}
