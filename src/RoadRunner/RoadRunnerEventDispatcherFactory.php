<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\EventDispatcher\RoadRunner;

use League\Event\EventDispatcher;
use League\Event\PrioritizedListenerRegistry;
use Psr\Container\ContainerInterface;
use Shlinkio\Shlink\EventDispatcher\Util\RequestIdProviderInterface;
use Spiral\RoadRunner\Jobs\Jobs;

use function Shlinkio\Shlink\Config\env;
use function Shlinkio\Shlink\EventDispatcher\resolveEnabledListenerChecker;
use function Shlinkio\Shlink\EventDispatcher\roadRunnerTaskListener;

class RoadRunnerEventDispatcherFactory
{
    public const ROAD_RUNNER_DISPATCHER = __NAMESPACE__ . '\RoadRunnerEventDispatcher';

    public function __invoke(ContainerInterface $container): EventDispatcher
    {
        $provider = new PrioritizedListenerRegistry();
        $eventsConfig = $container->get('config')['events'] ?? [];
        $requestIdProvider = $container->has(RequestIdProviderInterface::class)
            ? $container->get(RequestIdProviderInterface::class)
            : new class implements RequestIdProviderInterface {
                public function currentRequestId(): string
                {
                    return '-';
                }
            };

        $this->registerEvents($provider, $container, $requestIdProvider, $eventsConfig['async'] ?? []);

        return new EventDispatcher($provider);
    }

    private function registerEvents(
        PrioritizedListenerRegistry $provider,
        ContainerInterface $container,
        RequestIdProviderInterface $requestIdProvider,
        array $events,
    ): void {
        if (env('RR_MODE') === null) {
            return;
        }

        $jobs = $container->get(Jobs::class);
        $checker = resolveEnabledListenerChecker($container);

        foreach ($events as $eventName => $listeners) {
            foreach ($listeners as $listener) {
                if (! $checker->shouldRegisterListener($eventName, $listener, true)) {
                    continue;
                }

                $provider->subscribeTo($eventName, roadRunnerTaskListener($jobs, $listener, $requestIdProvider));
            }
        }
    }
}
