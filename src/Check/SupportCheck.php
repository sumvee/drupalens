<?php

declare(strict_types=1);

namespace Drupalens\Check;

use Drupalens\Data\ReleaseHistory;
use Drupalens\Finding\Finding;
use Drupalens\Finding\Severity;
use Drupalens\Project\Project;

/**
 * Flags end-of-life core and unsupported contrib projects.
 *
 * Core EOL is a known fact per major (7/8/9 are end-of-life; 10 and 11 are
 * supported). Contrib "unsupported" comes from the project_status in the
 * release-history feed (reliable). Branch-level support is intentionally
 * deferred: mapping a composer version to a drupal.org branch needs more
 * care and would risk false positives.
 */
final class SupportCheck implements Check
{
    /** Core majors that are end-of-life. */
    private const EOL_CORE_MAJORS = [7, 8, 9];

    public function id(): string
    {
        return 'support';
    }

    public function run(Project $project, ReleaseHistory $releases): array
    {
        $findings = [];

        if ($project->coreVersion !== null && preg_match('/^(\d+)\./', $project->coreVersion, $m)) {
            $major = (int) $m[1];
            if (in_array($major, self::EOL_CORE_MAJORS, true)) {
                $findings[] = new Finding(
                    Severity::High,
                    'EOL001',
                    sprintf('Drupal core %s is end-of-life', $project->coreVersion),
                    'drupal/core',
                    'upgrade to a supported major (10 or 11)',
                );
            }
        }

        foreach ($project->drupalProjects() as $pkg) {
            $short = $pkg->project();
            if ($short === null) {
                continue;
            }
            $history = $releases->fetch($short);
            if ($history === null) {
                continue;
            }
            if (!$history->isSupported()) {
                $findings[] = new Finding(
                    Severity::High,
                    'EOL002',
                    sprintf('%s is unsupported or obsolete on drupal.org', $pkg->name),
                    $pkg->name,
                    'find a maintained alternative',
                );
            }
        }
        return $findings;
    }
}
