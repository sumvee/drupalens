<?php

declare(strict_types=1);

namespace Drupalens\Data;

use RuntimeException;

/**
 * Parses a drupal.org release-history XML document into a ProjectReleases.
 * Kept separate from the HTTP client so it can be tested against captured
 * real payloads with no network.
 */
final class ReleaseHistoryParser
{
    public function parse(string $xml): ProjectReleases
    {
        $prev = libxml_use_internal_errors(true);
        $root = simplexml_load_string($xml);
        libxml_use_internal_errors($prev);

        if ($root === false) {
            throw new RuntimeException('invalid release-history XML');
        }
        // drupal.org returns <error>...</error> for an unknown project.
        if ($root->getName() === 'error') {
            throw new RuntimeException('project not found in release history');
        }

        $shortName = (string) ($root->short_name ?? '');
        $status = (string) ($root->project_status ?? '');
        $branches = array_values(array_filter(array_map(
            'trim',
            explode(',', (string) ($root->supported_branches ?? '')),
        ), static fn (string $b): bool => $b !== ''));

        $releases = [];
        if (isset($root->releases->release)) {
            foreach ($root->releases->release as $rel) {
                $releases[] = new Release(
                    version: (string) $rel->version,
                    status: (string) $rel->status,
                    security: $this->isSecurityRelease($rel),
                    date: isset($rel->date) ? (int) $rel->date : null,
                    coreCompatibility: isset($rel->core_compatibility) ? (string) $rel->core_compatibility : null,
                );
            }
        }

        return new ProjectReleases($shortName, $status, $branches, $releases);
    }

    private function isSecurityRelease(\SimpleXMLElement $rel): bool
    {
        if (!isset($rel->terms->term)) {
            return false;
        }
        foreach ($rel->terms->term as $term) {
            if ((string) $term->name === 'Release type' && (string) $term->value === 'Security update') {
                return true;
            }
        }
        return false;
    }
}
