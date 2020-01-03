<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\EventDispatcher;

use Laminas\ServiceManager\Proxy\LazyServiceFactory;
use Phly\EventDispatcher as Phly;
use Psr\EventDispatcher as Psr;

return [

    'events' => [
        'regular' => [],
        'async' => [],
    ],

    'dependencies' => [
        'factories' => [
            Phly\EventDispatcher::class => Phly\EventDispatcherFactory::class,
            Psr\ListenerProviderInterface::class => Listener\ListenerProviderFactory::class,
        ],
        'aliases' => [
            Psr\EventDispatcherInterface::class => Phly\EventDispatcher::class,
        ],
        'delegators' => [
            // The listener provider has to be lazy, because it uses the Swoole server to generate AsyncEventListeners
            // Without making this lazy, CLI commands which depend on the EventDispatcher fail
            Psr\ListenerProviderInterface::class => [
                LazyServiceFactory::class,
            ],
        ],
        'lazy_services' => [
            'class_map' => [
                Psr\ListenerProviderInterface::class => Psr\ListenerProviderInterface::class,
            ],
        ],
    ],

];
