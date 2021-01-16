<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\EventDispatcher\EventDispatcher;

use Mezzio\Swoole\Event\EventDispatcher as SwooleEventDispatcher;
use Mezzio\Swoole\Event\SwooleListenerProvider;
use Psr\Container\ContainerInterface;

use function Shlinkio\Shlink\EventDispatcher\lazyListener;

class SyncEventDispatcherFactory
{
    public const SYNC_EVENT_DISPATCHER = 'Shlinkio\Shlink\EventDispatcher\EventDispatcher\SyncEventDispatcher';

    public function __invoke(ContainerInterface $container): SwooleEventDispatcher
    {
        $provider = new SwooleListenerProvider();
        $eventsConfig = $container->get('config')['events'] ?? [];
        $fallback = $eventsConfig['fallback_async_to_regular'] ?? false;

        $this->registerEvents($provider, $container, $eventsConfig['regular'] ?? []);

        if ($fallback) {
            $this->registerEvents($provider, $container, $eventsConfig['async'] ?? []);
        }

        return new SwooleEventDispatcher($provider);
    }

    private function registerEvents(
        SwooleListenerProvider $provider,
        ContainerInterface $container,
        array $events
    ): void {
        foreach ($events as $eventName => $listeners) {
            foreach ($listeners as $listener) {
                $provider->addListener($eventName, lazyListener($container, $listener));
            }
        }
    }
}