<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\EventDispatcher\RoadRunner;

use JsonSerializable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shlinkio\Shlink\EventDispatcher\RoadRunner\RoadRunnerTaskListener;
use Spiral\RoadRunner\Jobs\JobsInterface;
use Spiral\RoadRunner\Jobs\QueueInterface;
use Spiral\RoadRunner\Jobs\Task\PreparedTaskInterface;
use Spiral\RoadRunner\Jobs\Task\QueuedTaskInterface;
use stdClass;

use function Shlinkio\Shlink\Json\json_encode;

class RoadRunnerTaskListenerTest extends TestCase
{
    private RoadRunnerTaskListener $listener;
    private MockObject & JobsInterface $jobs;
    private string $listenerServiceName = 'service';

    public function setUp(): void
    {
        $this->jobs = $this->createMock(JobsInterface::class);
        $this->listener = new RoadRunnerTaskListener($this->jobs, $this->listenerServiceName);
    }

    #[Test, DataProvider('provideEvents')]
    public function expectedTaskIsDispatchedBasedOnProvidedEvent(object $event, array $expectedPayload): void
    {
        $queue = $this->createMock(QueueInterface::class);
        $task = $this->createMock(PreparedTaskInterface::class);

        $this->jobs->expects($this->once())->method('connect')->with('shlink')->willReturn($queue);
        $queue->expects($this->once())->method('create')->with($event::class, json_encode([
            'listenerServiceName' => $this->listenerServiceName,
            'eventPayload' => $expectedPayload,
        ]))->willReturn($task);
        $queue->expects($this->once())->method('dispatch')->with($task)->willReturn(
            $this->createMock(QueuedTaskInterface::class),
        );

        ($this->listener)($event);
    }

    public static function provideEvents(): iterable
    {
        yield [new stdClass(), []];
        yield [
            new class implements JsonSerializable {
                public function jsonSerialize(): array
                {
                    return [
                        'foo' => 'bar',
                        'baz' => 123,
                    ];
                }
            },
            [
                'foo' => 'bar',
                'baz' => 123,
            ],
        ];
    }
}
