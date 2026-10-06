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

## Checks

```
  security  each drupal.org project vs the live release-history API:
            a newer SECURITY release -> critical (SEC001)
            a newer stable release   -> medium   (UPD001)
  support   end-of-life core majors 7/8/9 (EOL001); contrib projects
            marked unsupported on drupal.org (EOL002)
  hygiene   abandoned Composer packages, with replacement hints (HYG001)
```

Run one with `--only=security|support|hygiene`. Findings carry stable IDs,
a severity, and a one-line fix. `--fail-on=<severity>` (default `high`)
sets the CI exit gate; `--cache <dir>` reuses drupal.org responses.

## Status

Working, pre-1.0. PHP 8.1+, Symfony Console, Apache-2.0. Roadmap: a
deprecated-API scan for custom code, branch-level support, and a `--live`
mode for config drift.

## Install (once released)

```
  composer global require sumvee/drupalens
  # or download drupalens.phar from a GitHub release
```

## Author

Built by Sumit Vig.
