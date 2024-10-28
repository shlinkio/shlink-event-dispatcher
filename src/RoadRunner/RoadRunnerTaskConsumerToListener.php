<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\EventDispatcher\RoadRunner;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Shlinkio\Shlink\EventDispatcher\Util\JsonUnserializable;
use Spiral\RoadRunner\Jobs\ConsumerInterface;
use Throwable;

use function is_subclass_of;
use function Shlinkio\Shlink\Json\json_decode;

readonly class RoadRunnerTaskConsumerToListener
{
    public function __construct(
        private ConsumerInterface $consumer,
        private ContainerInterface $container,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @param (callable(string): void)|null $setCurrentRequestId
     */
    public function listenForTasks(callable|null $setCurrentRequestId = null): void
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

                [
                    'listenerServiceName' => $listenerService,
                    'eventPayload' => $payload,
                    'requestId' => $requestId,
                ] = json_decode($task->getPayload());
                if ($setCurrentRequestId !== null) {
                    $setCurrentRequestId($requestId);
                }

                $this->container->get($listenerService)($event::fromPayload($payload));
                $task->complete();
            } catch (Throwable $e) {
                $task->fail($e);
            }
        }
    }
}
