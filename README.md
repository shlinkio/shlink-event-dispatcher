# Shlink Event Dispatcher

This library simplifies registering async and regular [PSR-14](https://www.php-fig.org/psr/psr-14/) event listeners while using [openswoole](https://openswoole.com/) via [`mezzio/mezzio-swoole`](https://docs.mezzio.dev/mezzio-swoole/), or [RoadRunner](https://roadrunner.dev/).

Async ones are executed using openswoole's task system or RoadRunner's jobs. This library takes care of the boilerplate of registering events as async tasks/jobs, and you just interact with plain PSR-14 listeners and events.

Most of the elements it provides require a [PSR-11](https://www.php-fig.org/psr/psr-11/) container, and it's easy to integrate on [mezzio](https://github.com/mezzio/mezzio) applications thanks to the `ConfigProvider` it includes.

[![Build Status](https://img.shields.io/github/workflow/status/shlinkio/shlink-event-dispatcher/Continuous%20integration/main?logo=github&style=flat-square)](https://github.com/shlinkio/shlink-event-dispatcher/actions?query=workflow%3A%22Continuous+integration%22)
[![Code Coverage](https://img.shields.io/codecov/c/gh/shlinkio/shlink-event-dispatcher/main?style=flat-square)](https://app.codecov.io/gh/shlinkio/shlink-event-dispatcher)
[![Infection MSI](https://img.shields.io/endpoint?style=flat-square&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fshlinkio%2Fshlink-event-dispatcher%2Fmain)](https://dashboard.stryker-mutator.io/reports/github.com/shlinkio/shlink-event-dispatcher/main)
[![Latest Stable Version](https://img.shields.io/github/release/shlinkio/shlink-event-dispatcher.svg?style=flat-square)](https://packagist.org/packages/shlinkio/shlink-event-dispatcher)
[![License](https://img.shields.io/github/license/shlinkio/shlink-event-dispatcher.svg?style=flat-square)](https://github.com/shlinkio/shlink-event-dispatcher/blob/main/LICENSE)
[![Paypal donate](https://img.shields.io/badge/Donate-paypal-blue.svg?style=flat-square&logo=paypal&colorA=aaaaaa)](https://slnk.to/donate)

## Install

Install this library using composer:

    composer require shlinkio/shlink-event-dispatcher

> This library is also a mezzio module which provides its own `ConfigProvider`. Add it to your configuration to get everything automatically set up.

## Usage

This module allows to register both regular and asynchronous event listeners on a PSR-14 EventDispatcher.

Regular listeners are executed on the same process, blocking the dispatching of the HTTP request, while asynchronous listeners are delegated to a openswoole background task or RoadRunner job, making the request to resolve immediately.

If neither openswoole nor RoadRunner are found, async listeners are ignored by default, but you can choose to make them to be registered as regular listeners instead.

> **Note**
> * In order to be able to integrate with openswoole tasks, you need to install `mezzio/mezzio-swoole`.
> * In order to be able to integrate with RoadRunner jobs, you need to install `spiral/roadrunner-jobs`.

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

        'fallback_async_to_regular' => true, // Defaults to false

    ],

];
```

The `events` config entry has these blocks.

* `regular`: A list of events with all the listeners tha should be dispatched synchronously for each one of them.
* `async`: A list of events with all the listeners that should be executed as swoole/openswoole tasks for each one of them.
* `fallback_async_to_regular`: Tells if async event listeners should be dispatched as regular ones in case neither swoole nor openswoole are not installed. It is false by default.

In both cases, listeners are identified by their service name, making the services to be lazily resolved only when their corresponding event gets dispatched.
