# Shlink Event Dispatcher

This library provides a PSR-14 EventDispatcher which is capable of dispatching both regular listeners and async listeners which are executed using [swoole](https://www.swoole.co.uk/)'s task system.

Most of the elements it provides require a [PSR-11](https://www.php-fig.org/psr/psr-11/) container, and it's easy to integrate on [expressive](https://github.com/zendframework/zend-expressive) applications thanks to the `ConfigProvider` it includes.

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

## Usage

This module allows to register both regular and asynchronous event listeners on a PSR-14 EventDispatcher.

Regular listeners are executed on the same process, blocking the dispatching of the HTTP request, while asynchronous listeners are delegated to a swoole background task, making the request to resolve immediately.

If swoole is not installed, async listeners are ignored by default, but you can choose to make them to be registered as regular listeners instead.

In order to register listeners you have to use a configuration like this:

```php
<?php
declare(strict_types=1);

return [

    'events' => [

        'regular' => [
            'foo_event' => [
                App\EventListener\FooRegularListener::class,
                App\EventListener\AnotherFooListener::class,
            ],
            'bar_event' => [
                App\EventListener\FooRegularListener::class,
            ],
        ],

        'async' => [
            'foo_event' => [
                App\EventListener\FooAsyncListener::class,
            ],
        ],

    ],

];
```

The `events` config entry has two blocks.

* `regular`: A list of events with all the listeners tha should be dispatched synchronously for each one of them.
* `async`: A list of events with all the listeners tha should be executed as swoole tasks for each one of them.

In both cases, listeners are identified by their service name, making the services to be lazily resolved only when their corresponding event gets dispatched.
