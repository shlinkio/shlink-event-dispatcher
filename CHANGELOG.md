# CHANGELOG

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com), and this project adheres to [Semantic Versioning](https://semver.org).

## [4.1.0] - 2024-10-14
### Added
* *Nothing*

### Changed
* Forward request ID when an event dispatches a roadrunner task.

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* *Nothing*


## [4.0.0] - 2024-03-03
### Added
* *Nothing*

### Changed
* Update dependencies
* Update to PHPUnit 11

### Deprecated
* *Nothing*

### Removed
* Remove support for openswoole
* Remove infection and mutation tests

### Fixed
* *Nothing*


## [3.1.0] - 2023-11-25
### Added
* Add new mechanism to prevent listeners to be registered based on runtime conditions.
* Add support for PHP 8.3

### Changed
* *Nothing*

### Deprecated
* Deprecated support for openswoole.

### Removed
* Drop support for PHP 8.1

### Fixed
* Fix EventDispatcherAggregate, making sure it dispatches both regular and async listeners if the event is registered for both


## [3.0.0] - 2023-05-23
### Added
* *Nothing*

### Changed
* Migrated to roadrunner-jobs 4.0
* Migrated infection config to json5.
* Migrated from prophecy to PHPUnit mocks.
* Updated to PHPUnit 10 and migrate to PHPUnit 10.1 config format.

### Deprecated
* Dropped support for roadrunner-jobs 2.x

### Removed
* *Nothing*

### Fixed
* *Nothing*


## [2.6.0] - 2022-09-18
### Added
* [#46](https://github.com/shlinkio/shlink-event-dispatcher/issues/46) Added support for RoadRunner.

### Changed
* *Nothing*

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* *Nothing*


## [2.5.0] - 2022-08-06
### Added
* *Nothing*

### Changed
* Updated to shlink-config 2.0

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* *Nothing*


## [2.4.0] - 2022-06-03
### Added
* *Nothing*

### Changed
* Updated dependencies
* Updated to infection 0.26, enabling HTML reports.
* Added explicitly enabled composer plugins to composer.json.

### Deprecated
* *Nothing*

### Removed
* Dropped support for PHP 8.0

### Fixed
* *Nothing*


## [2.3.0] - 2021-12-12
### Added
* [#40](https://github.com/shlinkio/shlink-event-dispatcher/issues/40) Added support for openswoole.

### Changed
* *Nothing*

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* *Nothing*


## [2.2.0] - 2021-11-01
### Added
* *Nothing*

### Changed
* Added experimental builds under PHP 8.1
* Increased required phpstan level to 8
* Moved ci workflow to external repo and reused
* Updated to phpstan 1.0

### Deprecated
* *Nothing*

### Removed
* Dropped support for PHP 7.4

### Fixed
* *Nothing*


## [2.1.0] - 2021-02-13
### Added
* [#33](https://github.com/shlinkio/shlink-event-dispatcher/issues/33) Decoupled from `mezzio/mezzio-swoole`.

### Changed
* *Nothing*

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* *Nothing*


## [2.0.0] - 2021-01-17
### Added
* [#30](https://github.com/shlinkio/shlink-event-dispatcher/issues/30) Added support for `mezzio/mezzio-swoole` v3.x.

### Changed
* [#1](https://github.com/shlinkio/shlink-event-dispatcher/issues/1) Decoupled project from one specific psr-14 implementation.
* [#31](https://github.com/shlinkio/shlink-event-dispatcher/issues/31) Migrated build to Github Actions.

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* *Nothing*


## [1.6.0] - 2020-12-03
### Added
* [#27](https://github.com/shlinkio/shlink-event-dispatcher/issues/27) Replaced `phly/phly-event-dispatcher` dependency by `league/event`.

### Changed
* *Nothing*

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* *Nothing*


## [1.5.0] - 2020-11-01
### Added
* Added explicit support for PHP 8

### Changed
* [#21](https://github.com/shlinkio/shlink-event-dispatcher/issues/21) Updated `phpunit` to v9 and `infection` to v0.19.
* Added PHP 8 to the build matrix, allowing failures on it.

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* *Nothing*


## [1.4.0] - 2020-03-13
### Added
* *Nothing*

### Changed
* [#19](https://github.com/shlinkio/shlink-event-dispatcher/issues/19) Migrated from `shlinkio/shlink-common` to` shlinkio/shlink-config`.

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* *Nothing*


## [1.3.0] - 2020-01-03
### Added
* *Nothing*

### Changed
* [#14](https://github.com/shlinkio/shlink-event-dispatcher/issues/14) Updated coding-standard (v2.1) and phpstan (v0.12) dependencies.
* [#15](https://github.com/shlinkio/shlink-event-dispatcher/issues/15) Migrated from Zend Framework components to [Laminas](https://getlaminas.org/).

### Deprecated
* *Nothing*

### Removed
* [#13](https://github.com/shlinkio/shlink-event-dispatcher/issues/13) Dropped support for PHP 7.2 and 7.3.

### Fixed
* *Nothing*


## [1.2.0] - 2019-11-30
### Added
* *Nothing*

### Changed
* [#9](https://github.com/shlinkio/shlink-event-dispatcher/issues/9) Updated dependencies, including shlink-common, coding-standard and infection.

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* *Nothing*


## [1.1.1] - 2019-09-11
### Added
* *Nothing*

### Changed
* *Nothing*

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* [#7](https://github.com/shlinkio/shlink-event-dispatcher/issues/7) Added support for [shlink-common](https://github.com/shlinkio/shlink-common) v2.0.0.


## [1.1.0] - 2019-08-16
### Added
* [#5](https://github.com/shlinkio/shlink-event-dispatcher/issues/5) Added support for a new `fallback_async_to_regular` config flag which allows async listeners to be registered as regular instead of being ignored when swoole is not installed.
* [#1](https://github.com/shlinkio/shlink-event-dispatcher/issues/1) Added docs on how to use the module.

### Changed
* *Nothing*

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* *Nothing*


## [1.0.0] - 2019-08-12
### Added
* First stable release

### Changed
* *Nothing*

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* *Nothing*
