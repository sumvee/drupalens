<?php

declare(strict_types=1);

namespace Drupalens\Finding;

/**
 * One audit finding. `id` is a stable, greppable code (SEC001, EOL001, ...).
 */
final class Finding
{
    public function __construct(
        public readonly Severity $severity,
        public readonly string $id,
        public readonly string $title,
        public readonly ?string $location = null,
        public readonly ?string $fix = null,
    ) {
    }

    /**
     * @return array<string,string>
     */
    public function toArray(): array
    {
        $out = [
            'severity' => $this->severity->value,
            'id' => $this->id,
            'title' => $this->title,
        ];
        if ($this->location !== null) {
            $out['location'] = $this->location;
        }
        if ($this->fix !== null) {
            $out['fix'] = $this->fix;
        }
        return $out;
    }
}
