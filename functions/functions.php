<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\EventDispatcher;

use Psr\Container\ContainerInterface;
use Spiral\RoadRunner\Jobs\JobsInterface;

function lazyListener(ContainerInterface $container, string $listenerServiceName): callable
{
    return new Listener\LazyEventListener($container, $listenerServiceName);
}

function roadRunnerTaskListener(JobsInterface $jobs, string $listenerServiceName): callable
{
    return new RoadRunner\RoadRunnerTaskListener($jobs, $listenerServiceName);
}
