<?php

declare(strict_types=1);

namespace Drupalens\Deprecation;

use PhpParser\Error;
use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use PhpParser\ParserFactory;

/**
 * Scans PHP source for use of deprecated Drupal symbols (function calls,
 * constants, and static method calls) using nikic/php-parser.
 */
final class CodeScanner
{
    private Parser $parser;

    public function __construct(private readonly DeprecationMap $map)
    {
        $this->parser = (new ParserFactory())->createForNewestSupportedVersion();
    }

    /**
     * @return Hit[]
     */
    public function scan(string $code): array
    {
        try {
            $ast = $this->parser->parse($code);
        } catch (Error) {
            return []; // unparseable file: skip rather than fail the audit
        }
        if ($ast === null) {
            return [];
        }

        $visitor = new class ($this->map) extends NodeVisitorAbstract {
            /** @var Hit[] */
            public array $hits = [];

            public function __construct(private readonly DeprecationMap $map)
            {
            }

            public function enterNode(Node $node): null
            {
                if ($node instanceof FuncCall && $node->name instanceof Name) {
                    $d = $this->map->function($node->name->getLast());
                    if ($d !== null) {
                        $this->hits[] = new Hit($d, $node->getStartLine());
                    }
                } elseif ($node instanceof ConstFetch) {
                    $d = $this->map->constant($node->name->toString());
                    if ($d !== null) {
                        $this->hits[] = new Hit($d, $node->getStartLine());
                    }
                } elseif ($node instanceof StaticCall && $node->class instanceof Name && $node->name instanceof Identifier) {
                    $d = $this->map->staticMethod($node->class->getLast() . '::' . $node->name->toString());
                    if ($d !== null) {
                        $this->hits[] = new Hit($d, $node->getStartLine());
                    }
                }
                return null;
            }
        };

        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);
        $traverser->traverse($ast);

        return $visitor->hits;
    }
}
