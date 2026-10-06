<?php

declare(strict_types=1);

namespace Drupalens\Tests\Check;

use Drupalens\Check\SecurityCheck;
use Drupalens\Data\ProjectReleases;
use Drupalens\Data\Release;
use Drupalens\Finding\Finding;
use Drupalens\Finding\Severity;
use Drupalens\Project\Package;
use Drupalens\Project\Project;
use Drupalens\Tests\Support\FakeReleaseHistory;
use PHPUnit\Framework\TestCase;

final class SecurityCheckTest extends TestCase
{
    private function project(): Project
    {
        return new Project('/x', '10.3.6', [
            new Package('drupal/token', '1.9.0', 'drupal-module'),
            new Package('drupal/webform', '6.1.0', 'drupal-module'),
            new Package('drupal/current_mod', '2.0.0', 'drupal-module'),
        ]);
    }

    private function history(): FakeReleaseHistory
    {
        return new FakeReleaseHistory([
            'token' => new ProjectReleases('token', 'published', ['8.x-1.'], [
                new Release('8.x-1.15', 'published', security: true),
                new Release('8.x-1.9', 'published', security: false),
            ]),
            'webform' => new ProjectReleases('webform', 'published', ['6.2.', '6.3.'], [
                new Release('6.3.1', 'published', security: false),
                new Release('6.1.0', 'published', security: false),
            ]),
            'current_mod' => new ProjectReleases('current_mod', 'published', ['2.'], [
                new Release('2.0.0', 'published', security: false),
            ]),
        ]);
    }

    /**
     * @return Finding[]
     */
    private function collect(): array
    {
        return (new SecurityCheck())->run($this->project(), $this->history());
    }

    public function testSecurityUpdateIsCritical(): void
    {
        $f = $this->find('drupal/token');
        self::assertSame(Severity::Critical, $f->severity);
        self::assertSame('SEC001', $f->id);
        self::assertStringContainsString('8.x-1.15', $f->title);
    }

    public function testOutdatedNonSecurityIsMedium(): void
    {
        $f = $this->find('drupal/webform');
        self::assertSame(Severity::Medium, $f->severity);
        self::assertSame('UPD001', $f->id);
        self::assertStringContainsString('6.3.1', $f->title);
    }

    public function testCurrentModuleHasNoFinding(): void
    {
        foreach ($this->collect() as $f) {
            self::assertNotSame('drupal/current_mod', $f->location);
        }
    }

    private function find(string $location): Finding
    {
        foreach ($this->collect() as $f) {
            if ($f->location === $location) {
                return $f;
            }
        }
        self::fail("no finding for {$location}");
    }
}
