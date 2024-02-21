# Shlink Event Dispatcher

This library simplifies registering async and regular [PSR-14](https://www.php-fig.org/psr/psr-14/) event listeners while using [RoadRunner](https://roadrunner.dev/).

Async ones are executed using RoadRunner jobs. This library takes care of the boilerplate of registering events as async tasks/jobs, and you just interact with plain PSR-14 listeners and events.

Most of the elements it provides require a [PSR-11](https://www.php-fig.org/psr/psr-11/) container, and it's easy to integrate on [mezzio](https://github.com/mezzio/mezzio) applications thanks to the `ConfigProvider` it includes.

[![Build Status](https://img.shields.io/github/actions/workflow/status/shlinkio/shlink-event-dispatcher/ci.yml?branch=main&logo=github&style=flat-square)](https://github.com/shlinkio/shlink-event-dispatcher/actions/workflows/ci.yml?query=workflow%3A%22Continuous+integration%22)
[![Code Coverage](https://img.shields.io/codecov/c/gh/shlinkio/shlink-event-dispatcher/main?style=flat-square)](https://app.codecov.io/gh/shlinkio/shlink-event-dispatcher)
[![Latest Stable Version](https://img.shields.io/github/release/shlinkio/shlink-event-dispatcher.svg?style=flat-square)](https://packagist.org/packages/shlinkio/shlink-event-dispatcher)
[![License](https://img.shields.io/github/license/shlinkio/shlink-event-dispatcher.svg?style=flat-square)](https://github.com/shlinkio/shlink-event-dispatcher/blob/main/LICENSE)
[![Paypal donate](https://img.shields.io/badge/Donate-paypal-blue.svg?style=flat-square&logo=paypal&colorA=aaaaaa)](https://slnk.to/donate)

## Install

Install this library using composer:

    composer require shlinkio/shlink-event-dispatcher

> This library is also a mezzio module which provides its own `ConfigProvider`. Add it to your configuration to get everything automatically set up.

## Usage

This module allows to register both regular and asynchronous event listeners on a PSR-14 EventDispatcher.

Regular listeners are executed on the same process, blocking the dispatching of the HTTP request, while asynchronous listeners are delegated to a RoadRunner job, making the request to resolve immediately.

If RoadRunner is not found, async listeners are ignored by default, but you can choose to make them to be registered as regular listeners instead.

> **Note**
> In order to be able to integrate with RoadRunner jobs, you need to install `spiral/roadrunner-jobs`.

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

The `events` config entry has these blocks.

* `regular`: A list of events with all the listeners tha should be dispatched synchronously for each one of them.
* `async`: A list of events with all the listeners that should be executed as RoadRunner jobs for each one of them.

In both cases, listeners are identified by their service name, making the services to be lazily resolved only when their corresponding event gets dispatched.

### Dynamically skip registering listeners

Sometimes you want to provide a base list of listeners for an event, but you may want to skip them from being actually registered based on some logic known only at runtime.

For example, if you have a listener to send events to RabbitMQ, but the feature is optional and a user could have disabled it. In that case you could check at runtime if the feature is disabled, and skip the listener registration.

This module allows a `Shlinkio\Shlink\EventDispatcher\Listener\EnabledListenerCheckerInterface` service to be registered, and if it resolves an instance opf that same type, it will be used to decide if listeners should be registered or not.

The service registered there has to implement this method:

```php
public function shouldRegisterListener(string $event, string $listener, ContainerInterface $container): bool
{
    // Do some logic to determine if $service should be registered for $event
    // You have access to the service container, in case external resources need to be queried
    // Then return `true` or `false`
}
```

> If the service is not found, or it resolves an instance of the wrong type, a default implementation is used, which registers all listeners.
> ```php
> public function shouldRegisterListener(string $event, string $listener, ContainerInterface $container): bool
> {
>    return true;
> }
> ```
