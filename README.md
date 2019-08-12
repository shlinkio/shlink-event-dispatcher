# Shlink Event Dispatcher

This library provides a PSR-14 EventDispatcher which is capable of dispatching both regular listeners and async listeners which are run using [swoole]'s task system.

Most of the elements it provides require a [PSR-11] container, and it's easy to integrate on [expressive] applications thanks to the `ConfigProvider` it includes.

[![Build Status](https://img.shields.io/travis/shlinkio/shlink-event-dispatcher.svg?style=flat-square)](https://travis-ci.org/shlinkio/shlink-event-dispatcher)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/shlinkio/shlink-event-dispatcher.svg?style=flat-square)](https://scrutinizer-ci.com/g/shlinkio/shlink-event-dispatcher/?branch=master)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/shlinkio/shlink-event-dispatcher.svg?style=flat-square)](https://scrutinizer-ci.com/g/shlinkio/shlink-event-dispatcher/?branch=master)
[![Latest Stable Version](https://img.shields.io/github/release/shlinkio/shlink-event-dispatcher.svg?style=flat-square)](https://packagist.org/packages/shlinkio/shlink-event-dispatcher)
[![License](https://img.shields.io/github/license/shlinkio/shlink-event-dispatcher.svg?style=flat-square)](https://github.com/shlinkio/shlink-event-dispatcher/blob/master/LICENSE)
[![Paypal donate](https://img.shields.io/badge/Donate-paypal-blue.svg?style=flat-square&logo=paypal&colorA=aaaaaa)](https://acel.me/donate)

## Install

Install this library using composer:

    composer require shlinkio/shlink-event-dispatcher

> This library is also an expressive module which provides its own `ConfigProvider`. Add it to your configuration to get everything automatically set up.
