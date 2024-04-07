<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\EventDispatcher\RoadRunner;

use JsonSerializable;
use Shlinkio\Shlink\EventDispatcher\Util\RequestIdProviderInterface;
use Spiral\RoadRunner\Jobs\JobsInterface;

use function Shlinkio\Shlink\Json\json_encode;

readonly class RoadRunnerTaskListener
{
    private const SHLINK_QUEUE = 'shlink';

    public function __construct(
        private JobsInterface $jobs,
        private string $listenerServiceName,
        private RequestIdProviderInterface $requestIdProvider,
    ) {
    }

    public function __invoke(object $event): void
    {
        $queue = $this->jobs->connect(self::SHLINK_QUEUE);
        $task = $queue->create($event::class, json_encode([
            'listenerServiceName' => $this->listenerServiceName,
            'eventPayload' => $event instanceof JsonSerializable ? $event : [],
            'requestId' => $this->requestIdProvider->currentRequestId(),
        ]));
        $queue->dispatch($task);
    }
}
