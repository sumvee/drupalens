<?php

declare(strict_types=1);

namespace Drupalens\Check;

use Drupalens\Data\ReleaseHistory;
use Drupalens\Finding\Finding;
use Drupalens\Finding\Severity;
use Drupalens\Project\Project;
use Drupalens\Version\VersionNormalizer;

/**
 * Compares each installed drupal.org project against its release-history:
 * a newer security release -> critical (SEC001); otherwise a newer stable
 * release -> medium (UPD001).
 */
final class SecurityCheck implements Check
{
    public function __construct(private readonly VersionNormalizer $versions = new VersionNormalizer())
    {
    }

    public function id(): string
    {
        return 'security';
    }

    public function run(Project $project, ReleaseHistory $releases): array
    {
        $findings = [];
        foreach ($project->drupalProjects() as $pkg) {
            $short = $pkg->project();
            if ($short === null) {
                continue;
            }
            $history = $releases->fetch($short);
            if ($history === null) {
                continue;
            }
            $installed = $pkg->version;

            $security = null;
            foreach ($history->releases as $rel) {
                if ($rel->isPublished() && $rel->security && $this->versions->isNewer($rel->version, $installed)) {
                    $security = $rel;
                    break;
                }
            }
            if ($security !== null) {
                $findings[] = new Finding(
                    Severity::Critical,
                    'SEC001',
                    sprintf('%s %s has a security update available (%s)', $pkg->name, $installed, $security->version),
                    $pkg->name,
                    sprintf('update %s to %s', $pkg->name, $security->version),
                );
                continue;
            }

            $latest = $history->latest();
            if ($latest !== null && $this->versions->isNewer($latest->version, $installed)) {
                $findings[] = new Finding(
                    Severity::Medium,
                    'UPD001',
                    sprintf('%s %s is outdated (latest %s)', $pkg->name, $installed, $latest->version),
                    $pkg->name,
                    sprintf('update %s to %s', $pkg->name, $latest->version),
                );
            }
        }
        return $findings;
    }
}
