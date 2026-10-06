# drupalens

A lens on your Drupal site's health, from the command line. Point it at a
Drupal codebase and get one prioritized report: insecure and outdated
modules, core/module support status, and project hygiene.

```
  drupalens audit .                 full report
  drupalens audit . --json          machine-readable findings
  drupalens audit . --fail-on=high  exit non-zero for CI gating
  drupalens audit . --only=security run a single check
```

Static by default (reads `composer.lock`, `*.info.yml`, config), so it runs
on any Drupal repo with no database and no bootstrap. Security and
outdated-module data is read live from the drupal.org release-history API
and cross-checked against Packagist advisories (never bundled stale).

## Status

Pre-release, in active development. PHP 8.1+, Symfony Console. Apache-2.0.

```
WAVE 0  skeleton ................. in progress
WAVE 1  project model (loader) ... next
WAVE 2  drupal.org + Packagist data layer
WAVE 3  checks: security, support, hygiene
WAVE 4  prioritized report + --json + exit codes
WAVE 5  release (Packagist + PHAR via Box + CI)
```

## Install (once released)

```
  composer global require sumvee/drupalens
  # or download drupalens.phar from a GitHub release
```

## Author

Built by Sumit Vig.
