<?php

declare(strict_types=1);

namespace Drupalens\Check;

use Drupalens\Data\ReleaseHistory;
use Drupalens\Finding\Finding;
use Drupalens\Finding\Severity;
use Drupalens\Project\Project;

/**
 * Project-hygiene checks that need no network: abandoned packages (with a
 * suggested replacement when composer provides one).
 */
final class HygieneCheck implements Check
{
    public function id(): string
    {
        return 'hygiene';
    }

    public function run(Project $project, ReleaseHistory $releases): array
    {
        $findings = [];
        foreach ($project->packages as $pkg) {
            if (!$pkg->abandoned) {
                continue;
            }
            $fix = $pkg->replacement !== null
                ? sprintf('replace %s with %s', $pkg->name, $pkg->replacement)
                : sprintf('replace %s with a maintained package', $pkg->name);

            $findings[] = new Finding(
                Severity::Medium,
                'HYG001',
                sprintf('%s is abandoned', $pkg->name),
                $pkg->name,
                $fix,
            );
        }
        return $findings;
    }
}
