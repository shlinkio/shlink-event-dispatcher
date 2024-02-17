<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\EventDispatcher\Dispatcher;

use Psr\Container\ContainerInterface;
use Shlinkio\Shlink\EventDispatcher\RoadRunner\RoadRunnerEventDispatcherFactory;

class EventDispatcherAggregateFactory
{
    public function __invoke(ContainerInterface $container): EventDispatcherAggregate
    {
        $asyncDispatcher = $container->get(RoadRunnerEventDispatcherFactory::ROAD_RUNNER_DISPATCHER);
        $regularDispatcher = $container->get(SyncEventDispatcherFactory::SYNC_DISPATCHER);
        $eventsConfig = $container->get('config')['events'] ?? [];

        return new EventDispatcherAggregate($asyncDispatcher, $regularDispatcher, $eventsConfig);
    }
}
