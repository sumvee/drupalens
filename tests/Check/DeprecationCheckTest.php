<?php

declare(strict_types=1);

namespace Drupalens\Tests\Check;

use Drupalens\Check\DeprecationCheck;
use Drupalens\Finding\Severity;
use Drupalens\Project\Project;
use Drupalens\Tests\Support\FakeReleaseHistory;
use PHPUnit\Framework\TestCase;

final class DeprecationCheckTest extends TestCase
{
    private function fixture(): string
    {
        return __DIR__ . '/../fixtures/custom-project';
    }

    public function testFindsDeprecationsInCustomCode(): void
    {
        $project = new Project($this->fixture(), '10.3.6', []);
        $findings = (new DeprecationCheck())->run($project, new FakeReleaseHistory());

        self::assertNotEmpty($findings);
        foreach ($findings as $f) {
            self::assertSame('DEP001', $f->id);
            self::assertStringContainsString('web/modules/custom/mymodule/mymodule.module:', (string) $f->location);
        }
        $titles = implode("\n", array_map(static fn ($f) => $f->title, $findings));
        self::assertStringContainsString('drupal_set_message', $titles);
        self::assertStringContainsString('db_query', $titles);
    }

    public function testSeverityHighWhenRemovedInInstalledCore(): void
    {
        // db_query is removed in 9; on core 10 the code is broken -> High.
        $project = new Project($this->fixture(), '10.3.6', []);
        $findings = (new DeprecationCheck())->run($project, new FakeReleaseHistory());
        self::assertSame(Severity::High, $this->forSymbol($findings, 'db_query')->severity);
    }

    public function testSeverityMediumWhenNotYetRemovedInCore(): void
    {
        // On core 8, db_query (removed in 9) is deprecated but not yet gone -> Medium.
        $project = new Project($this->fixture(), '8.9.20', []);
        $findings = (new DeprecationCheck())->run($project, new FakeReleaseHistory());
        self::assertSame(Severity::Medium, $this->forSymbol($findings, 'db_query')->severity);
    }

    /**
     * @param \Drupalens\Finding\Finding[] $findings
     */
    private function forSymbol(array $findings, string $symbol): \Drupalens\Finding\Finding
    {
        foreach ($findings as $f) {
            if (str_contains($f->title, $symbol)) {
                return $f;
            }
        }
        self::fail("no finding for {$symbol}");
    }
}
