<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\EventDispatcher;

use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Laminas\ServiceManager\Proxy\LazyServiceFactory;
use Mezzio\Swoole\Event\EventDispatcherInterface as SwooleEventDispatcherInterface;
use Mezzio\Swoole\Event\SwooleListenerProvider;
use Psr\EventDispatcher as Psr;
use Psr\Log\LoggerInterface;
use Shlinkio\Shlink\EventDispatcher\Listener\AsyncListenersProviderDelegator;

return [

    'events' => [
        'regular' => [],
        'async' => [],
    ],

    'dependencies' => [
        'factories' => [
            Listener\TaskFinishListener::class => ConfigAbstractFactory::class,
            Dispatcher\SyncEventDispatcherFactory::SYNC_EVENT_DISPATCHER =>
                Dispatcher\SyncEventDispatcherFactory::class,
        ],
        'aliases' => [
            Psr\EventDispatcherInterface::class => SwooleEventDispatcherInterface::class,
        ],
        'delegators' => [
            Dispatcher\SyncEventDispatcherFactory::SYNC_EVENT_DISPATCHER => [
                LazyServiceFactory::class,
            ],
            SwooleListenerProvider::class => [
                AsyncListenersProviderDelegator::class,
            ],
        ],
        'lazy_services' => [
            'class_map' => [
                Dispatcher\SyncEventDispatcherFactory::SYNC_EVENT_DISPATCHER =>
                    Psr\EventDispatcherInterface::class,
            ],
        ],
    ],

    ConfigAbstractFactory::class => [
        Listener\TaskFinishListener::class => [LoggerInterface::class],
    ],

];
