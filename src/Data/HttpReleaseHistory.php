<?php

declare(strict_types=1);

namespace Drupalens\Data;

use Drupalens\Version;

/**
 * Fetches release-history from drupal.org over HTTP, with an optional
 * on-disk cache for repeatable/offline runs.
 *
 * Note: the cache has no TTL by design (explicit --cache opt-in for CI).
 * Do not point a long-lived cache at a security gate; clear it to refresh.
 */
final class HttpReleaseHistory implements ReleaseHistory
{
    private ReleaseHistoryParser $parser;

    public function __construct(
        private readonly ?string $cacheDir = null,
        ?ReleaseHistoryParser $parser = null,
    ) {
        $this->parser = $parser ?? new ReleaseHistoryParser();
    }

    public function fetch(string $project): ?ProjectReleases
    {
        $xml = $this->retrieve($project);
        if ($xml === null) {
            return null;
        }
        try {
            return $this->parser->parse($xml);
        } catch (\Throwable) {
            return null;
        }
    }

    private function retrieve(string $project): ?string
    {
        if ($this->cacheDir !== null && is_file($this->cachePath($project))) {
            $cached = file_get_contents($this->cachePath($project));
            if ($cached !== false) {
                return $cached;
            }
        }

        $url = sprintf('https://updates.drupal.org/release-history/%s/current', rawurlencode($project));
        $context = stream_context_create(['http' => [
            'method' => 'GET',
            'header' => 'User-Agent: drupalens/' . Version::VALUE,
            'timeout' => 15,
            'ignore_errors' => true,
        ]]);

        $body = @file_get_contents($url, false, $context);
        if ($body === false) {
            return null;
        }
        if ($this->statusCode($http_response_header ?? []) >= 400) {
            return null;
        }

        if ($this->cacheDir !== null) {
            @mkdir($this->cacheDir, 0775, true);
            @file_put_contents($this->cachePath($project), $body);
        }
        return $body;
    }

    private function cachePath(string $project): string
    {
        $safe = preg_replace('/[^A-Za-z0-9._-]/', '_', $project) ?? $project;
        return rtrim((string) $this->cacheDir, '/') . '/' . $safe . '.xml';
    }

    /**
     * @param string[] $headers
     */
    private function statusCode(array $headers): int
    {
        $code = 200;
        foreach ($headers as $line) {
            if (preg_match('#^HTTP/\S+\s+(\d+)#', $line, $m)) {
                $code = (int) $m[1];
            }
        }
        return $code;
    }
}
