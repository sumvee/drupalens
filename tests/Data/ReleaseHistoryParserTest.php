<?php

declare(strict_types=1);

namespace Drupalens\Tests\Data;

use Drupalens\Data\ReleaseHistoryParser;
use PHPUnit\Framework\TestCase;

/**
 * Parses a captured, real drupal.org release-history payload (token.xml).
 */
final class ReleaseHistoryParserTest extends TestCase
{
    private function parseToken(): \Drupalens\Data\ProjectReleases
    {
        $xml = file_get_contents(__DIR__ . '/../fixtures/release-history/token.xml');
        self::assertIsString($xml);
        return (new ReleaseHistoryParser())->parse($xml);
    }

    public function testProjectMetadata(): void
    {
        $p = $this->parseToken();
        self::assertSame('token', $p->shortName);
        self::assertSame('published', $p->projectStatus);
        self::assertTrue($p->isSupported());
        self::assertContains('8.x-1.', $p->supportedBranches);
    }

    public function testReleasesParsed(): void
    {
        $p = $this->parseToken();
        self::assertCount(24, $p->releases);
        $latest = $p->latest();
        self::assertNotNull($latest);
        self::assertSame('8.x-1.17', $latest->version);
        self::assertSame('published', $latest->status);
        self::assertStringContainsString('11', (string) $latest->coreCompatibility);
    }

    public function testSupportedBranchMembership(): void
    {
        $p = $this->parseToken();
        self::assertTrue($p->isOnSupportedBranch('8.x-1.15'));
        self::assertFalse($p->isOnSupportedBranch('7.x-1.0'));
    }

    public function testLatestTokenReleaseIsNotSecurity(): void
    {
        // token 8.x-1.17 is a "Bug fixes" release.
        $p = $this->parseToken();
        self::assertFalse($p->latest()?->security);
    }

    public function testSecurityReleaseDetection(): void
    {
        // Minimal document using the real element structure confirmed from
        // the captured token payload (terms/term/name+value).
        $xml = '<project><short_name>x</short_name><project_status>published</project_status>'
            . '<supported_branches>2.</supported_branches><releases>'
            . '<release><version>2.0.1</version><status>published</status>'
            . '<terms><term><name>Release type</name><value>Security update</value></term></terms></release>'
            . '<release><version>2.0.0</version><status>published</status>'
            . '<terms><term><name>Release type</name><value>Bug fixes</value></term></terms></release>'
            . '</releases></project>';
        $p = (new ReleaseHistoryParser())->parse($xml);
        self::assertTrue($p->releases[0]->security);
        self::assertFalse($p->releases[1]->security);
        self::assertSame('2.0.1', $p->latestSecurity()?->version);
    }

    public function testErrorDocumentThrows(): void
    {
        $this->expectExceptionMessageMatches('/not found/');
        (new ReleaseHistoryParser())->parse('<error>No release history was found for the requested project.</error>');
    }
}
