<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\EventDispatcher\RoadRunner;

use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Spiral\RoadRunner\Jobs\Consumer;
use Spiral\RoadRunner\Jobs\Jobs;
use Spiral\RoadRunner\Jobs\Serializer\JsonSerializer;
use Spiral\RoadRunner\Worker;
use Spiral\RoadRunner\WorkerInterface;

return [

    'dependencies' => [
        'factories' => [
            RoadRunnerEventDispatcherFactory::ROAD_RUNNER_DISPATCHER => RoadRunnerEventDispatcherFactory::class,
            RoadRunnerTaskConsumerToListener::class => static fn (ContainerInterface $c)
                => new RoadRunnerTaskConsumerToListener(
                    $c->get(Consumer::class),
                    $c,
                    $c->get(LoggerInterface::class),
                ),
            JsonSerializer::class => InvokableFactory::class,
            Worker::class => static fn () => Worker::create(),
            Jobs::class => static fn (ContainerInterface $c) => new Jobs(null, $c->get(JsonSerializer::class)),
            Consumer::class => ConfigAbstractFactory::class,
        ],
        'aliases' => [
            WorkerInterface::class => Worker::class,
        ],
    ],

    ConfigAbstractFactory::class => [
        Consumer::class => [Worker::class, JsonSerializer::class],
    ],

];
