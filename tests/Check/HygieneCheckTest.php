<?php

declare(strict_types=1);

namespace Drupalens\Tests\Check;

use Drupalens\Check\HygieneCheck;
use Drupalens\Finding\Severity;
use Drupalens\Project\Package;
use Drupalens\Project\Project;
use Drupalens\Tests\Support\FakeReleaseHistory;
use PHPUnit\Framework\TestCase;

final class HygieneCheckTest extends TestCase
{
    public function testAbandonedPackageFlaggedWithReplacement(): void
    {
        $project = new Project('/x', '10.3.6', [
            new Package('drupal/token', '1.9.0', 'drupal-module'),
            new Package('drupal/legacy_thing', '1.0.0', 'drupal-module', abandoned: true, replacement: 'drupal/better_thing'),
        ]);

        $findings = (new HygieneCheck())->run($project, new FakeReleaseHistory());

        self::assertCount(1, $findings);
        self::assertSame('HYG001', $findings[0]->id);
        self::assertSame(Severity::Medium, $findings[0]->severity);
        self::assertSame('drupal/legacy_thing', $findings[0]->location);
        self::assertStringContainsString('drupal/better_thing', (string) $findings[0]->fix);
    }

    public function testNoAbandonedNoFindings(): void
    {
        $project = new Project('/x', '10.3.6', [
            new Package('drupal/token', '1.9.0', 'drupal-module'),
        ]);
        self::assertSame([], (new HygieneCheck())->run($project, new FakeReleaseHistory()));
    }
}
