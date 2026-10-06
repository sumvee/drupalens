<?php

declare(strict_types=1);

namespace Drupalens\Report;

use Drupalens\Finding\Finding;
use Drupalens\Finding\Severity;

/**
 * Renders findings as a prioritized text report grouped by severity.
 */
final class TextReport
{
    /**
     * @param Finding[] $findings
     */
    public function render(array $findings): string
    {
        if ($findings === []) {
            return "No issues found.\n";
        }

        $out = '';
        foreach ([Severity::Critical, Severity::High, Severity::Medium, Severity::Low, Severity::Info] as $sev) {
            $group = array_values(array_filter($findings, static fn (Finding $f): bool => $f->severity === $sev));
            if ($group === []) {
                continue;
            }
            $out .= strtoupper($sev->value) . "\n";
            foreach ($group as $f) {
                $out .= sprintf("  [%s] %s\n", $f->id, $f->title);
                if ($f->fix !== null) {
                    $out .= sprintf("        fix: %s\n", $f->fix);
                }
            }
            $out .= "\n";
        }

        return $out . $this->summary($findings);
    }

    /**
     * @param Finding[] $findings
     */
    private function summary(array $findings): string
    {
        $counts = [];
        foreach ($findings as $f) {
            $counts[$f->severity->value] = ($counts[$f->severity->value] ?? 0) + 1;
        }
        $parts = [];
        foreach ($counts as $sev => $n) {
            $parts[] = "{$n} {$sev}";
        }
        return count($findings) . ' finding(s): ' . implode(', ', $parts) . "\n";
    }
}
