<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\EventDispatcher\RoadRunner;

use League\Event\EventDispatcher;
use League\Event\PrioritizedListenerRegistry;
use Psr\Container\ContainerInterface;
use Spiral\RoadRunner\Jobs\Jobs;

use function Shlinkio\Shlink\EventDispatcher\roadRunnerTaskListener;

class RoadRunnerEventDispatcherFactory
{
    public const ROAD_RUNNER_DISPATCHER = __NAMESPACE__ . '\RoadRunnerEventDispatcher';

    public function __invoke(ContainerInterface $container): EventDispatcher
    {
        $provider = new PrioritizedListenerRegistry();
        $eventsConfig = $container->get('config')['events'] ?? [];

        $this->registerEvents($provider, $eventsConfig['async'] ?? []);

        return new EventDispatcher($provider);
    }

    private function registerEvents(PrioritizedListenerRegistry $provider, array $events): void
    {
        $jobs = new Jobs();
        if (! $jobs->isAvailable()) {
            return;
        }

        foreach ($events as $eventName => $listeners) {
            foreach ($listeners as $listener) {
                $provider->subscribeTo($eventName, roadRunnerTaskListener($jobs, $listener));
            }
        }
    }
}
