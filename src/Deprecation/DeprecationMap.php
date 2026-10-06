<?php

declare(strict_types=1);

namespace Drupalens\Deprecation;

use RuntimeException;

/**
 * The curated map of deprecated Drupal API symbols, loaded from
 * data/deprecations.json. This is a seed subset of well-known
 * deprecations, not exhaustive; it is the in-repo expertise artifact and
 * is extended over time.
 */
final class DeprecationMap
{
    /** @var array<string,Deprecation> */
    private array $functions = [];
    /** @var array<string,Deprecation> */
    private array $constants = [];
    /** @var array<string,Deprecation> */
    private array $staticMethods = [];

    /**
     * @param Deprecation[] $entries
     */
    public function __construct(array $entries)
    {
        foreach ($entries as $d) {
            match ($d->kind) {
                'function' => $this->functions[$d->symbol] = $d,
                'constant' => $this->constants[$d->symbol] = $d,
                'staticmethod' => $this->staticMethods[$d->symbol] = $d,
                default => null,
            };
        }
    }

    public static function default(): self
    {
        return self::fromFile(dirname(__DIR__, 2) . '/data/deprecations.json');
    }

    public static function fromFile(string $path): self
    {
        $raw = file_get_contents($path);
        if ($raw === false) {
            throw new RuntimeException("cannot read deprecation map at {$path}");
        }
        /** @var list<array<string,string>> $rows */
        $rows = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

        $entries = array_map(static fn (array $r): Deprecation => new Deprecation(
            $r['symbol'],
            $r['kind'],
            $r['deprecated_in'],
            $r['removed_in'],
            $r['replacement'],
        ), $rows);

        return new self($entries);
    }

    public function function(string $name): ?Deprecation
    {
        return $this->functions[$name] ?? null;
    }

    public function constant(string $name): ?Deprecation
    {
        return $this->constants[$name] ?? null;
    }

    public function staticMethod(string $classAndMethod): ?Deprecation
    {
        return $this->staticMethods[$classAndMethod] ?? null;
    }
}
