<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\EventDispatcher\RoadRunner;

use Psr\Container\ContainerInterface;
use Spiral\RoadRunner\Jobs\Consumer;
use Spiral\RoadRunner\Jobs\Serializer\JsonSerializer;

class RoadRunnerTaskConsumerToListenerFactory
{
    public function __invoke(ContainerInterface $container): RoadRunnerTaskConsumerToListener
    {
        return new RoadRunnerTaskConsumerToListener(
            new Consumer(null, new JsonSerializer()),
            $container,
        );
    }
}
