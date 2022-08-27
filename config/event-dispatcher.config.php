<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\EventDispatcher;

use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Mezzio\Swoole\Event\SwooleListenerProvider;
use Psr\EventDispatcher as Psr;
use Psr\Log\LoggerInterface;

return [

    'events' => [
        'regular' => [],
        'async' => [],
    ],

    'dependencies' => [
        'factories' => [
            Swoole\TaskFinishListener::class => ConfigAbstractFactory::class,
            Dispatcher\SyncEventDispatcherFactory::SYNC_DISPATCHER => Dispatcher\SyncEventDispatcherFactory::class,
            Dispatcher\EventDispatcherAggregate::class => Dispatcher\EventDispatcherAggregateFactory::class,
        ],

        'aliases' => [
            Psr\EventDispatcherInterface::class => Dispatcher\EventDispatcherAggregate::class,
        ],

        'delegators' => [
            SwooleListenerProvider::class => [
                Swoole\SwooleListenersProviderDelegator::class,
            ],
        ],
    ],

    ConfigAbstractFactory::class => [
        Swoole\TaskFinishListener::class => [LoggerInterface::class],
    ],

];
