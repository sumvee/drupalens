<?php

declare(strict_types=1);

namespace Drupalens\Check;

use Drupalens\Data\ReleaseHistory;
use Drupalens\Finding\Finding;
use Drupalens\Project\Project;

interface Check
{
    /** Short id for selection via --only (security|support|hygiene). */
    public function id(): string;

    /**
     * @return Finding[]
     */
    public function run(Project $project, ReleaseHistory $releases): array;
}
