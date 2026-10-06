<?php

declare(strict_types=1);

namespace Drupalens\Check;

use Drupalens\Data\ReleaseHistory;
use Drupalens\Deprecation\CodeScanner;
use Drupalens\Deprecation\DeprecationMap;
use Drupalens\Finding\Finding;
use Drupalens\Finding\Severity;
use Drupalens\Project\Project;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Scans a project's CUSTOM code for use of deprecated Drupal APIs.
 * Contrib and core are intentionally skipped (not the user's code).
 *
 * Severity depends on the installed core: a symbol already removed in the
 * installed major is High (the code is broken there); a symbol merely
 * deprecated is Medium (an upgrade risk).
 */
final class DeprecationCheck implements Check
{
    private const CUSTOM_DIRS = [
        'web/modules/custom', 'web/themes/custom', 'web/profiles/custom',
        'modules/custom', 'themes/custom', 'profiles/custom',
    ];
    private const EXTENSIONS = ['php', 'module', 'inc', 'theme', 'install', 'profile'];

    private CodeScanner $scanner;

    public function __construct(?DeprecationMap $map = null, ?CodeScanner $scanner = null)
    {
        $map ??= DeprecationMap::default();
        $this->scanner = $scanner ?? new CodeScanner($map);
    }

    public function id(): string
    {
        return 'deprecation';
    }

    public function run(Project $project, ReleaseHistory $releases): array
    {
        $coreMajor = null;
        if ($project->coreVersion !== null && preg_match('/^(\d+)/', $project->coreVersion, $m)) {
            $coreMajor = (int) $m[1];
        }

        $findings = [];
        foreach ($this->customFiles($project->path) as $file) {
            $code = @file_get_contents($file);
            if ($code === false) {
                continue;
            }
            foreach ($this->scanner->scan($code) as $hit) {
                $dep = $hit->deprecation;
                $severity = ($coreMajor !== null && $dep->removedInMajor() <= $coreMajor)
                    ? Severity::High
                    : Severity::Medium;

                $findings[] = new Finding(
                    $severity,
                    'DEP001',
                    sprintf('%s is deprecated (removed in Drupal %s)', $dep->symbol, $dep->removedIn),
                    $this->relative($project->path, $file) . ':' . $hit->line,
                    'use ' . $dep->replacement,
                );
            }
        }
        return $findings;
    }

    /**
     * @return string[]
     */
    private function customFiles(string $path): array
    {
        $files = [];
        foreach (self::CUSTOM_DIRS as $dir) {
            $full = rtrim($path, '/') . '/' . $dir;
            if (!is_dir($full)) {
                continue;
            }
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($full, FilesystemIterator::SKIP_DOTS),
            );
            foreach ($iterator as $entry) {
                /** @var \SplFileInfo $entry */
                if ($entry->isFile() && in_array(strtolower($entry->getExtension()), self::EXTENSIONS, true)) {
                    $files[] = $entry->getPathname();
                }
            }
        }
        sort($files);
        return $files;
    }

    private function relative(string $base, string $file): string
    {
        $base = rtrim($base, '/') . '/';
        return str_starts_with($file, $base) ? substr($file, strlen($base)) : $file;
    }
}
