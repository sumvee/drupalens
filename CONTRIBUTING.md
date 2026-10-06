# Contributing to drupalens

Thanks for your interest. drupalens is Apache-2.0 licensed; by contributing
you agree your contribution is licensed under the same terms.

## Dev loop

```
  composer install
  vendor/bin/phpunit                         run the tests
  php bin/drupalens audit <drupal-project>   try a change end to end
```

Requires PHP 8.1+. No Drupal bootstrap and no database: drupalens reads a
project statically (composer.lock, info.yml) and queries drupal.org at
audit time.

## Where things live

```
  src/Project    static model from composer.lock (Loader, Project, Package)
  src/Data       drupal.org release-history client + parser (+ cache)
  src/Version    version normalizer (composer vs drupal.org dialects)
  src/Check      SecurityCheck, SupportCheck, HygieneCheck (+ Check iface)
  src/Finding    Severity, Finding
  src/Audit      Auditor (runs checks, sorts findings)
  src/Report     TextReport, JsonReport
  src/Command    the audit command (Symfony Console)
```

## Rules for the checks and data

```
  - security/version TRUTH comes live from drupal.org (and Packagist);
    never bundle a stale advisory snapshot
  - compare versions through VersionNormalizer (composer "1.9.0" vs
    drupal.org "8.x-1.17"); when a version is undetermined, do not emit a
    finding rather than guess
  - prefer a missed finding over a false positive (e.g. branch-level
    support is deferred until it can be done without false alarms)
  - every check has offline tests via tests/Support/FakeReleaseHistory
```

## Pull requests

```
  1 branch from main
  2 add/extend tests (vendor/bin/phpunit stays green on PHP 8.1 and 8.3)
  3 php -l clean; one logical change per PR
  4 stable finding IDs (SEC/UPD/EOL/HYG...) are a public contract: add new
    ones, do not repurpose existing ones
```

## Reporting issues

Open a GitHub issue with the `composer.lock` excerpt (or the module +
version) and the command you ran.
