# Changelog

All notable changes to drupalens are documented here.
Format follows Keep a Changelog; versions follow Semantic Versioning.

## [Unreleased]

## [0.1.0]

```
FIRST PUBLIC RELEASE
  a static Drupal auditor: security, support status, hygiene -> one report
  PHP 8.1+ · Symfony Console · Apache-2.0
```

### Added
- `audit <path>`: audits a Drupal codebase statically (reads composer.lock;
  no Drupal bootstrap, no database).
- Security + outdated check: each drupal.org project is compared against
  the live release-history API; a newer security release is reported as
  critical (SEC001), a newer stable release as medium (UPD001).
- Support check: end-of-life core majors (7/8/9 -> EOL001) and contrib
  projects marked unsupported on drupal.org (EOL002).
- Hygiene check: abandoned Composer packages with replacement hints
  (HYG001).
- Prioritized report grouped by severity, `--json` output, and a
  `--fail-on` severity gate (default high) with a non-zero exit for CI.
- `--only` to run a single check; `--cache` to reuse drupal.org responses.
- Version normalizer reconciling composer.lock semantic versions with
  drupal.org legacy versions (8.x-1.17 -> 1.17.0).
- Distribution: Composer (Packagist) and a Box-built PHAR on each release.

### Notes
- Branch-level contrib support is not yet enforced (deferred to avoid
  false positives); the deprecated-API scan is on the roadmap.
- Security data is read live from drupal.org and is never bundled.

[Unreleased]: https://github.com/sumvee/drupalens/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/sumvee/drupalens/releases/tag/v0.1.0
