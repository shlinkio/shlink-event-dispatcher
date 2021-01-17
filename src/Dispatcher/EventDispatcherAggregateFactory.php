<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\EventDispatcher\Dispatcher;

use Mezzio\Swoole\Event\EventDispatcherInterface as SwooleEventDispatcherInterface;
use Psr\Container\ContainerInterface;

class EventDispatcherAggregateFactory
{
    public function __invoke(ContainerInterface $container): EventDispatcherAggregate
    {
        $asyncDispatcher = $container->get(SwooleEventDispatcherInterface::class);
        $regularDispatcher = $container->get(SyncEventDispatcherFactory::SYNC_DISPATCHER);
        $eventsConfig = $container->get('config')['events'] ?? [];

        return new EventDispatcherAggregate($asyncDispatcher, $regularDispatcher, $eventsConfig);
    }
}
