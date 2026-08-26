# Changelog

All notable changes to `laravel-actionable-diagnostics` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.3] - 2026-08-26

### Added
- Standard 5-section gold `RELEASE-GATE.md` certification layout.
- Cross-platform centered Hero header in `README.md` (`<h1 align="center">` + `<p align="center">`) for identical rendering across GitHub and Packagist.
- Short `Audit: Verified` badge and high-value navigation Quick Links.

## [1.0.2] - 2026-08-26

### Added
- Standardized top-level `config/actionable-diagnostics.php` configuration file.
- Opt-in HTTP ingestion routes configuration (`actionable-diagnostics.routes.enabled` and `prefix`).
- Export-ignore `.gitattributes` for streamlined Packagist archive distribution.
- Complete PHPStan Level MAX compliance with full generic iterable type annotations.
- Dynamic test suite bootstrapping via `tests/bootstrap.php`.

### Changed
- Refactored `WebhookDispatcher` to utilize standard `Illuminate\Support\Facades\Http` client.
- Updated documentation with 7-color badge design system and Release Gate verification.

## [1.0.1] - 2026-08-18

### Changed
- Updated documentation and badges.

## [1.0.0] - 2026-08-15

### Added
- Initial release of multichannel diagnostic event engine and actionable exception framework.
