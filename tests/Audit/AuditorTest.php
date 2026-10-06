<?php

declare(strict_types=1);

namespace Drupalens\Tests\Audit;

use Drupalens\Audit\Auditor;
use Drupalens\Check\HygieneCheck;
use Drupalens\Check\SecurityCheck;
use Drupalens\Check\SupportCheck;
use Drupalens\Data\ProjectReleases;
use Drupalens\Data\Release;
use Drupalens\Finding\Severity;
use Drupalens\Project\Package;
use Drupalens\Project\Project;
use Drupalens\Tests\Support\FakeReleaseHistory;
use PHPUnit\Framework\TestCase;

final class AuditorTest extends TestCase
{
    private function project(): Project
    {
        return new Project('/x', '9.5.11', [ // EOL core -> high
            new Package('drupal/token', '1.9.0', 'drupal-module'), // security -> critical
            new Package('drupal/legacy_thing', '1.0.0', 'drupal-module', abandoned: true, replacement: 'drupal/better_thing'), // hygiene -> medium
        ]);
    }

    private function history(): FakeReleaseHistory
    {
        return new FakeReleaseHistory([
            'token' => new ProjectReleases('token', 'published', ['8.x-1.'], [
                new Release('8.x-1.15', 'published', security: true),
                new Release('8.x-1.9', 'published', security: false),
            ]),
            // legacy_thing not on drupal.org -> null -> skipped by security/support
        ]);
    }

    private function auditor(): Auditor
    {
        return new Auditor(new SecurityCheck(), new SupportCheck(), new HygieneCheck());
    }

    public function testFindingsSortedMostSevereFirst(): void
    {
        $findings = $this->auditor()->run($this->project(), $this->history());
        $ids = array_map(static fn ($f) => $f->id, $findings);
        // critical (SEC001), then high (EOL001), then medium (HYG001)
        self::assertSame(['SEC001', 'EOL001', 'HYG001'], $ids);
        self::assertSame(Severity::Critical, $findings[0]->severity);
    }

    public function testOnlyFiltersToOneCheck(): void
    {
        $findings = $this->auditor()->run($this->project(), $this->history(), 'hygiene');
        self::assertCount(1, $findings);
        self::assertSame('HYG001', $findings[0]->id);
    }

    public function testIds(): void
    {
        self::assertSame(['security', 'support', 'hygiene'], $this->auditor()->ids());
    }
}
