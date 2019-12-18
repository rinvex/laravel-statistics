# Rinvex Statistics Change Log

All notable changes to this project will be documented in this file.

This project adheres to [Semantic Versioning](CONTRIBUTING.md).


## [v3.0.1] - 2019-12-18
- Fix `migrate:reset` args as it doesn't accept --step

## [v3.0.0] - 2019-09-23
- Upgrade to Laravel v6 and update dependencies

## [v2.1.1] - 2019-06-03
- Enforce latest composer package versions

## [v2.1.0] - 2019-06-02
- Update composer deps
- Drop PHP 7.1 travis test
- Refactor migrations and artisan commands, and tweak service provider publishes functionality

## [v2.0.0] - 2019-03-03
- Rename environment variable QUEUE_DRIVER to QUEUE_CONNECTION
- Require PHP 7.2 & Laravel 5.8
- Apply PHPUnit 8 updates

## [v1.0.2] - 2018-12-22
- Update composer dependencies
- Add PHP 7.3 support to travis
- Fix MySQL / PostgreSQL json column compatibility

## [v1.0.1] - 2018-10-05
- Fix wrong composer package version constraints

## [v1.0.0] - 2018-10-01
- Enforce Consistency
- Support Laravel 5.7+
- Rename package to rinvex/laravel-statistics

## [v0.0.2] - 2018-09-21
- Update travis php versions
- Define polymorphic relationship parameters explicitly
- Require import package rinvex/countries
- Update timezone validation rule
- Drop StyleCI multi-language support (paid feature now!)
- Update composer dependencies
- Prepare and tweak testing configuration
- Update StyleCI options
- Highlight variables in strings explicitly
- Update PHPUnit options

## v0.0.1 - 2018-02-18
- Tag first release

[v3.0.1]: https://github.com/rinvex/laravel-statistics/compare/v3.0.0...v3.0.1
[v3.0.0]: https://github.com/rinvex/laravel-statistics/compare/v2.1.1...v3.0.0
[v2.1.1]: https://github.com/rinvex/laravel-statistics/compare/v2.1.0...v2.1.1
[v2.1.0]: https://github.com/rinvex/laravel-statistics/compare/v2.0.0...v2.1.0
[v2.0.0]: https://github.com/rinvex/laravel-statistics/compare/v1.0.2...v2.0.0
[v1.0.2]: https://github.com/rinvex/laravel-statistics/compare/v1.0.1...v1.0.2
[v1.0.1]: https://github.com/rinvex/laravel-statistics/compare/v1.0.0...v1.0.1
[v1.0.0]: https://github.com/rinvex/laravel-statistics/compare/v0.0.2...v1.0.0
[v0.0.2]: https://github.com/rinvex/laravel-statistics/compare/v0.0.1...v0.0.2
