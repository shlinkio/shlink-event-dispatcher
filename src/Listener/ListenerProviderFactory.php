<?php
declare(strict_types=1);

namespace Shlinkio\Shlink\EventDispatcher\Listener;

use Phly\EventDispatcher\ListenerProvider\AttachableListenerProvider;
use Psr\Container\ContainerInterface;
use Swoole\Http\Server as HttpServer;

use function Phly\EventDispatcher\lazyListener;
use function Shlinkio\Shlink\EventDispatcher\asyncListener;

class ListenerProviderFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $events = $config['events'] ?? [];
        $fallbackAsyncToRegular = $events['fallback_async_to_regular'] ?? false;
        $provider = new AttachableListenerProvider();

        $this->registerListeners($events['regular'] ?? [], $container, $provider, false);
        $this->registerListeners($events['async'] ?? [], $container, $provider, ! $fallbackAsyncToRegular);

        return $provider;
    }

    private function registerListeners(
        array $events,
        ContainerInterface $container,
        AttachableListenerProvider $provider,
        bool $isAsync
    ): void {
        if (empty($events)) {
            return;
        }

        // Avoid registering async event listeners when the swoole server is not registered
        if ($isAsync && ! $container->has(HttpServer::class)) {
            return;
        }

        /**
         * @var string $eventName
         * @var array $listeners
         */
        foreach ($events as $eventName => $listeners) {
            foreach ($listeners as $listenerName) {
                $eventListener = $isAsync
                    ? asyncListener($container->get(HttpServer::class), $listenerName)
                    : lazyListener($container, $listenerName);

                $provider->listen($eventName, $eventListener);
            }
        }
    }
}
