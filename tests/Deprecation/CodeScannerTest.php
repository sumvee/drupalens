<?php

declare(strict_types=1);

namespace Drupalens\Tests\Deprecation;

use Drupalens\Deprecation\CodeScanner;
use Drupalens\Deprecation\DeprecationMap;
use PHPUnit\Framework\TestCase;

final class CodeScannerTest extends TestCase
{
    private function scanner(): CodeScanner
    {
        return new CodeScanner(DeprecationMap::default());
    }

    public function testFindsFunctionConstantAndStaticCall(): void
    {
        $code = <<<'PHP'
        <?php
        function x() {
            drupal_set_message('hi');
            $t = REQUEST_TIME;
            $u = \Drupal::url('route');
            db_query('SELECT 1');
            $ok = strlen('fine'); // not deprecated
        }
        PHP;

        $hits = $this->scanner()->scan($code);
        $symbols = array_map(static fn ($h) => $h->deprecation->symbol, $hits);
        sort($symbols);

        self::assertSame(
            ['Drupal::url', 'REQUEST_TIME', 'db_query', 'drupal_set_message'],
            $symbols,
        );
    }

    public function testRecordsLineNumbers(): void
    {
        $code = "<?php\n\ndrupal_set_message('x');\n";
        $hits = $this->scanner()->scan($code);
        self::assertCount(1, $hits);
        self::assertSame(3, $hits[0]->line);
    }

    public function testUnparseableReturnsNoHits(): void
    {
        self::assertSame([], $this->scanner()->scan('<?php this is not valid php {{{'));
    }

    public function testCleanCodeHasNoHits(): void
    {
        $code = "<?php\nfunction y(){ return \\Drupal::messenger()->addMessage('ok'); }\n";
        self::assertSame([], $this->scanner()->scan($code));
    }
}
