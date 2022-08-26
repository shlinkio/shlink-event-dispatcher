<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\EventDispatcher\RoadRunner;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Spiral\RoadRunner\Jobs\Consumer;
use Spiral\RoadRunner\Jobs\Serializer\JsonSerializer;
use Spiral\RoadRunner\WorkerInterface;

class RoadRunnerTaskConsumerToListenerFactory
{
    public function __invoke(ContainerInterface $container): RoadRunnerTaskConsumerToListener
    {
        $worker = $container->has(WorkerInterface::class) ? $container->get(WorkerInterface::class) : null;
        return new RoadRunnerTaskConsumerToListener(
            new Consumer($worker, new JsonSerializer()),
            $container,
            $container->get(LoggerInterface::class),
        );
    }
}
