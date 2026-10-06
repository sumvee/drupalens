<?php

declare(strict_types=1);

namespace Drupalens\Finding;

enum Severity: string
{
    case Critical = 'critical';
    case High = 'high';
    case Medium = 'medium';
    case Low = 'low';
    case Info = 'info';

    /** Higher rank = more severe. Used by the --fail-on gate and ordering. */
    public function rank(): int
    {
        return match ($this) {
            self::Critical => 4,
            self::High => 3,
            self::Medium => 2,
            self::Low => 1,
            self::Info => 0,
        };
    }

    /**
     * Resolve a --fail-on threshold. "none" means never fail on severity.
     */
    public static function threshold(string $value): ?self
    {
        $value = strtolower(trim($value));
        if ($value === 'none') {
            return null;
        }
        return self::tryFrom($value);
    }
}
