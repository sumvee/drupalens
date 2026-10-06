<?php

declare(strict_types=1);

namespace Drupalens\Tests\Version;

use Drupalens\Version\VersionNormalizer;
use PHPUnit\Framework\TestCase;

final class VersionNormalizerTest extends TestCase
{
    /**
     * @dataProvider cases
     */
    public function testNormalize(string $in, ?string $expected): void
    {
        self::assertSame($expected, (new VersionNormalizer())->normalize($in));
    }

    /**
     * @return array<string, array{0:string,1:?string}>
     */
    public static function cases(): array
    {
        return [
            'legacy'        => ['8.x-1.17', '1.17.0'],
            'legacy older'  => ['7.x-1.0', '1.0.0'],
            'semantic'      => ['2.1.0', '2.1.0'],
            'v-prefixed'    => ['v6.4.1', '6.4.1'],
            'prerelease'    => ['2.0.0-beta1', '2.0.0'],
            'major.minor'   => ['1.17', '1.17.0'],
            'legacy dev'    => ['8.x-1.x-dev', null],
            'garbage'       => ['dev-main', null],
        ];
    }

    public function testIsNewerAcrossDialects(): void
    {
        $vn = new VersionNormalizer();
        // drupal.org "8.x-1.15" (-> 1.15.0) is newer than locked "1.9.0"
        self::assertTrue($vn->isNewer('8.x-1.15', '1.9.0'));
        self::assertFalse($vn->isNewer('8.x-1.9', '1.9.0'));
        self::assertTrue($vn->isNewer('6.3.1', '6.1.0'));
        // undetermined versions never report newer
        self::assertFalse($vn->isNewer('8.x-1.x-dev', '1.9.0'));
    }
}
