<?php

declare(strict_types=1);

namespace Drupalens\Report;

use Drupalens\Finding\Finding;

/**
 * Renders findings as JSON for pipelines and other tools.
 */
final class JsonReport
{
    /**
     * @param Finding[] $findings
     */
    public function render(array $findings): string
    {
        $payload = [
            'findings' => array_map(static fn (Finding $f): array => $f->toArray(), $findings),
            'summary' => $this->summary($findings),
        ];
        return json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    }

    /**
     * @param Finding[] $findings
     * @return array<string,int>
     */
    private function summary(array $findings): array
    {
        $counts = ['total' => count($findings)];
        foreach ($findings as $f) {
            $counts[$f->severity->value] = ($counts[$f->severity->value] ?? 0) + 1;
        }
        return $counts;
    }
}
