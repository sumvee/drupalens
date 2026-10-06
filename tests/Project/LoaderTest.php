<?php

declare(strict_types=1);

namespace Drupalens\Tests\Project;

use Drupalens\Project\Loader;
use Drupalens\Project\Package;
use PHPUnit\Framework\TestCase;

final class LoaderTest extends TestCase
{
    private function fixture(): string
    {
        return __DIR__ . '/../fixtures/sample-project';
    }

    public function testLoadsCoreVersion(): void
    {
        $project = (new Loader())->load($this->fixture());
        self::assertSame('10.3.6', $project->coreVersion);
    }

    public function testLoadsAllPackages(): void
    {
        $project = (new Loader())->load($this->fixture());
        // 7 runtime + 1 dev
        self::assertCount(8, $project->packages);
    }

    public function testDrupalProjectsOnlyModulesAndThemes(): void
    {
        $project = (new Loader())->load($this->fixture());
        $names = array_map(static fn (Package $p): string => $p->name, $project->drupalProjects());
        sort($names);
        self::assertSame(
            ['drupal/bootstrap', 'drupal/legacy_thing', 'drupal/token', 'drupal/webform'],
            $names,
        );
        // core is excluded from drupalProjects()
        self::assertNotContains('drupal/core', $names);
    }

    public function testProjectShortName(): void
    {
        $project = (new Loader())->load($this->fixture());
        $token = $this->packageNamed($project->packages, 'drupal/token');
        self::assertSame('token', $token->project());
        self::assertTrue($token->isDrupalOrg());
        self::assertTrue($token->isModuleOrTheme());
    }

    public function testAbandonedDetection(): void
    {
        $project = (new Loader())->load($this->fixture());
        $legacy = $this->packageNamed($project->packages, 'drupal/legacy_thing');
        self::assertTrue($legacy->abandoned);
        self::assertSame('drupal/better_thing', $legacy->replacement);
    }

    public function testNonDrupalPackages(): void
    {
        $project = (new Loader())->load($this->fixture());
        $names = array_map(static fn (Package $p): string => $p->name, $project->nonDrupalPackages());
        self::assertContains('symfony/console', $names);
        self::assertContains('guzzlehttp/guzzle', $names);
        self::assertNotContains('drupal/token', $names);
    }

    public function testMissingLockThrows(): void
    {
        $this->expectExceptionMessageMatches('/no composer\.lock/');
        (new Loader())->load(sys_get_temp_dir() . '/drupalens-nope-' . uniqid());
    }

    /**
     * @param Package[] $packages
     */
    private function packageNamed(array $packages, string $name): Package
    {
        foreach ($packages as $p) {
            if ($p->name === $name) {
                return $p;
            }
        }
        self::fail("package {$name} not found");
    }
}
