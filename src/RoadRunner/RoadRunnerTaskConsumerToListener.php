<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\EventDispatcher\RoadRunner;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Shlinkio\Shlink\EventDispatcher\Util\JsonUnserializable;
use Spiral\RoadRunner\Jobs\ConsumerInterface;
use Throwable;

use function is_subclass_of;

class RoadRunnerTaskConsumerToListener
{
    public function __construct(
        private readonly ConsumerInterface $consumer,
        private readonly ContainerInterface $container,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function listenForTasks(): void
    {
        while ($task = $this->consumer->waitTask()) {
            try {
                $event = $task->getName();
                if (! is_subclass_of($event, JsonUnserializable::class)) {
                    $this->logger->warning(
                        'It was not possible to process task for event "{event}", because it does not '
                        . 'implement {implements}',
                        ['event' => $event, 'implements' => JsonUnserializable::class],
                    );
                    $task->complete();
                    continue;
                }

                ['listenerServiceName' => $listener, 'eventPayload' => $payload] = $task->getPayload();
                $this->container->get($listener)($event::fromPayload($payload));
                $task->complete();
            } catch (Throwable $e) {
                $task->fail($e);
            }
        }
    }
}
