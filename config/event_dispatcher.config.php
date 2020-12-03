<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\EventDispatcher;

use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Laminas\ServiceManager\Proxy\LazyServiceFactory;
use League\Event as League;
use Psr\EventDispatcher as Psr;

return [

    'events' => [
        'regular' => [],
        'async' => [],
    ],

    'dependencies' => [
        'factories' => [
            League\EventDispatcher::class => ConfigAbstractFactory::class,
            Psr\ListenerProviderInterface::class => Listener\ListenerProviderFactory::class,
        ],
        'aliases' => [
            Psr\EventDispatcherInterface::class => League\EventDispatcher::class,
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

    ConfigAbstractFactory::class => [
        League\EventDispatcher::class => [Psr\ListenerProviderInterface::class],
    ],

];
