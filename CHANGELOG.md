# CHANGELOG

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com), and this project adheres to [Semantic Versioning](https://semver.org).

## [Unreleased]
### Added
* *Nothing*

### Changed
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
