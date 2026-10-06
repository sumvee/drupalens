<?php

declare(strict_types=1);

namespace Drupalens\Tests\Check;

use Drupalens\Check\SupportCheck;
use Drupalens\Data\ProjectReleases;
use Drupalens\Data\Release;
use Drupalens\Project\Package;
use Drupalens\Project\Project;
use Drupalens\Tests\Support\FakeReleaseHistory;
use PHPUnit\Framework\TestCase;

final class SupportCheckTest extends TestCase
{
    public function testEolCoreFlagged(): void
    {
        $project = new Project('/x', '9.5.11', []);
        $findings = (new SupportCheck())->run($project, new FakeReleaseHistory());
        self::assertCount(1, $findings);
        self::assertSame('EOL001', $findings[0]->id);
        self::assertStringContainsString('9.5.11', $findings[0]->title);
    }

    public function testSupportedCoreNotFlagged(): void
    {
        $project = new Project('/x', '11.4.8', []);
        $findings = (new SupportCheck())->run($project, new FakeReleaseHistory());
        self::assertSame([], $findings);
    }

    public function testUnsupportedContribFlagged(): void
    {
        $project = new Project('/x', '10.3.6', [
            new Package('drupal/obsolete', '1.0.0', 'drupal-module'),
        ]);
        $history = new FakeReleaseHistory([
            'obsolete' => new ProjectReleases('obsolete', 'unsupported', [], [
                new Release('1.0.0', 'published', security: false),
            ]),
        ]);
        $findings = (new SupportCheck())->run($project, $history);
        self::assertCount(1, $findings);
        self::assertSame('EOL002', $findings[0]->id);
        self::assertSame('drupal/obsolete', $findings[0]->location);
    }
}
