<?php

declare(strict_types=1);

namespace Drupalens\Tests\Deprecation;

use Drupalens\Deprecation\DeprecationMap;
use PHPUnit\Framework\TestCase;

final class DeprecationMapTest extends TestCase
{
    public function testDefaultLoads(): void
    {
        $map = DeprecationMap::default();

        $fn = $map->function('drupal_set_message');
        self::assertNotNull($fn);
        self::assertSame('9.0.0', $fn->removedIn);
        self::assertSame(9, $fn->removedInMajor());
        self::assertStringContainsString('messenger', $fn->replacement);

        self::assertNotNull($map->constant('REQUEST_TIME'));
        self::assertNotNull($map->staticMethod('Drupal::url'));
        self::assertNull($map->function('strlen'));
    }
}
