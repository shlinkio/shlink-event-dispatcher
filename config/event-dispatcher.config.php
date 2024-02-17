<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\EventDispatcher;

use Psr\EventDispatcher as Psr;

return [

    'events' => [
        'regular' => [],
        'async' => [],
    ],

    'dependencies' => [
        'factories' => [
            Dispatcher\SyncEventDispatcherFactory::SYNC_DISPATCHER => Dispatcher\SyncEventDispatcherFactory::class,
            Dispatcher\EventDispatcherAggregate::class => Dispatcher\EventDispatcherAggregateFactory::class,
        ],

        'aliases' => [
            Psr\EventDispatcherInterface::class => Dispatcher\EventDispatcherAggregate::class,
        ],
    ],

];
