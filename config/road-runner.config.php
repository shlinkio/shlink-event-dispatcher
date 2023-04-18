<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\EventDispatcher\RoadRunner;

use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Spiral\Goridge\RPC\RPC;
use Spiral\RoadRunner\Environment;
use Spiral\RoadRunner\Jobs\Consumer;
use Spiral\RoadRunner\Jobs\Jobs;
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

            Environment::class => static fn () => Environment::fromGlobals(),

            Jobs::class => ConfigAbstractFactory::class,
            RPC::class => static fn (ContainerInterface $c) => RPC::create(
                $c->get(Environment::class)->getRPCAddress(),
            ),

            Consumer::class => ConfigAbstractFactory::class,
            Worker::class => static fn (ContainerInterface $c) => Worker::createFromEnvironment(
                $c->get(Environment::class),
            ),
        ],
        'aliases' => [
            WorkerInterface::class => Worker::class,
        ],
    ],

    ConfigAbstractFactory::class => [
        Jobs::class => [RPC::class],
        Consumer::class => [Worker::class],
    ],

];
