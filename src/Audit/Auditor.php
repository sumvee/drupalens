<?php

declare(strict_types=1);

namespace Drupalens\Audit;

use Drupalens\Check\Check;
use Drupalens\Data\ReleaseHistory;
use Drupalens\Finding\Finding;
use Drupalens\Project\Project;

/**
 * Runs the registered checks and returns findings sorted most-severe first.
 */
final class Auditor
{
    /** @var Check[] */
    private array $checks;

    public function __construct(Check ...$checks)
    {
        $this->checks = $checks;
    }

    /**
     * @return string[] the ids of the registered checks
     */
    public function ids(): array
    {
        return array_map(static fn (Check $c): string => $c->id(), $this->checks);
    }

    /**
     * @return Finding[]
     */
    public function run(Project $project, ReleaseHistory $releases, ?string $only = null): array
    {
        $findings = [];
        foreach ($this->checks as $check) {
            if ($only !== null && $check->id() !== $only) {
                continue;
            }
            foreach ($check->run($project, $releases) as $finding) {
                $findings[] = $finding;
            }
        }

        usort(
            $findings,
            static fn (Finding $a, Finding $b): int
                => ($b->severity->rank() <=> $a->severity->rank()) ?: strcmp($a->id, $b->id),
        );

        return $findings;
    }
}
