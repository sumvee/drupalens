<?php

declare(strict_types=1);

namespace Drupalens\Tests\Report;

use Drupalens\Finding\Finding;
use Drupalens\Finding\Severity;
use Drupalens\Report\JsonReport;
use Drupalens\Report\TextReport;
use PHPUnit\Framework\TestCase;

final class ReportTest extends TestCase
{
    /**
     * @return Finding[]
     */
    private function findings(): array
    {
        return [
            new Finding(Severity::Critical, 'SEC001', 'drupal/token 1.9.0 has a security update available (8.x-1.15)', 'drupal/token', 'update drupal/token to 8.x-1.15'),
            new Finding(Severity::Medium, 'HYG001', 'drupal/legacy_thing is abandoned', 'drupal/legacy_thing', 'replace drupal/legacy_thing with drupal/better_thing'),
        ];
    }

    public function testTextReport(): void
    {
        $out = (new TextReport())->render($this->findings());
        self::assertStringContainsString('CRITICAL', $out);
        self::assertStringContainsString('[SEC001]', $out);
        self::assertStringContainsString('fix: update drupal/token', $out);
        self::assertStringContainsString('2 finding(s)', $out);
    }

    public function testTextReportEmpty(): void
    {
        self::assertSame("No issues found.\n", (new TextReport())->render([]));
    }

    public function testJsonReport(): void
    {
        $out = (new JsonReport())->render($this->findings());
        $data = json_decode($out, true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(2, $data['summary']['total']);
        self::assertSame(1, $data['summary']['critical']);
        self::assertSame('SEC001', $data['findings'][0]['id']);
        self::assertSame('update drupal/token to 8.x-1.15', $data['findings'][0]['fix']);
        // slashes not escaped
        self::assertStringContainsString('drupal/token', $out);
    }
}
