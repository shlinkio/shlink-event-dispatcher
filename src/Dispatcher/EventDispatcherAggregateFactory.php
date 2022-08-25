<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\EventDispatcher\Dispatcher;

use Mezzio\Swoole\Event\EventDispatcherInterface as SwooleEventDispatcherInterface;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Shlinkio\Shlink\EventDispatcher\RoadRunner\RoadRunnerEventDispatcherFactory;

class EventDispatcherAggregateFactory
{
    public function __invoke(ContainerInterface $container): EventDispatcherAggregate
    {
        $asyncDispatcher = $this->resolveAsyncDispatcher($container);
        $regularDispatcher = $container->get(SyncEventDispatcherFactory::SYNC_DISPATCHER);
        $eventsConfig = $container->get('config')['events'] ?? [];

        return new EventDispatcherAggregate($asyncDispatcher, $regularDispatcher, $eventsConfig);
    }

    private function resolveAsyncDispatcher(ContainerInterface $container): EventDispatcherInterface
    {
        if (! $container->has(SwooleEventDispatcherInterface::class)) {
            // This dispatcher will actually not register anything if RoadRunner is not available
            return $container->get(RoadRunnerEventDispatcherFactory::ROAD_RUNNER_DISPATCHER);
        }

        return $container->get(SwooleEventDispatcherInterface::class);
    }
}
