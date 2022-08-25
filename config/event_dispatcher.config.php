<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\EventDispatcher;

use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Mezzio\Swoole\Event\SwooleListenerProvider;
use Psr\EventDispatcher as Psr;
use Psr\Log\LoggerInterface;
use Shlinkio\Shlink\EventDispatcher\Listener\SwooleListenersProviderDelegator;

return [

    'events' => [
        'regular' => [],
        'async' => [],
    ],

    'dependencies' => [
        'factories' => [
            Listener\TaskFinishListener::class => ConfigAbstractFactory::class,
            Dispatcher\SyncEventDispatcherFactory::SYNC_DISPATCHER => Dispatcher\SyncEventDispatcherFactory::class,
            Dispatcher\EventDispatcherAggregate::class => Dispatcher\EventDispatcherAggregateFactory::class,
        ],

        'aliases' => [
            Psr\EventDispatcherInterface::class => Dispatcher\EventDispatcherAggregate::class,
        ],

        'delegators' => [
            SwooleListenerProvider::class => [
                SwooleListenersProviderDelegator::class,
            ],
        ],
    ],

    ConfigAbstractFactory::class => [
        Listener\TaskFinishListener::class => [LoggerInterface::class],
    ],

];
