<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\EventDispatcher\Listener;

use Laminas\ServiceManager\ServiceManager;
use Mezzio\Swoole\Event\SwooleListenerProvider;
use Mezzio\Swoole\Task\DeferredServiceListenerDelegator;
use Psr\Container\ContainerInterface;

use function Shlinkio\Shlink\EventDispatcher\lazyListener;

class AsyncListenersProviderDelegator
{
    /**
     * @param ContainerInterface|ServiceManager $container
     */
    public function __invoke(ContainerInterface $container, string $s, callable $factory): SwooleListenerProvider
    {
        /** @var SwooleListenerProvider $provider */
        $provider = $factory();

        $asyncEvents = $container->get('config')['events']['async'] ?? [];

        foreach ($asyncEvents as $eventName => $listeners) {
            foreach ($listeners as $listenerName) {
                $provider->addListener($eventName, lazyListener($container, $listenerName));
                $container->addDelegator($listenerName, DeferredServiceListenerDelegator::class);
            }
        }

        return $provider;
    }
}
