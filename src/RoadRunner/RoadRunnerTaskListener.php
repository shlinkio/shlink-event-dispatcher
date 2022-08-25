<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\EventDispatcher\RoadRunner;

use JsonSerializable;
use Spiral\RoadRunner\Jobs\JobsInterface;

class RoadRunnerTaskListener
{
    private const SHLINK_QUEUE = 'shlink';

    public function __construct(private readonly JobsInterface $jobs, private readonly string $listenerServiceName)
    {
    }

    public function __invoke(object $event): void
    {
        $queue = $this->jobs->connect(self::SHLINK_QUEUE);
        $task = $queue->create($event::class, [
            'listenerServiceName' => $this->listenerServiceName,
            'eventPayload' => $event instanceof JsonSerializable ? $event->jsonSerialize() : [],
        ]);
        $queue->dispatch($task);
    }
}
