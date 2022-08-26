<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\EventDispatcher\RoadRunner;

use JsonSerializable;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Shlinkio\Shlink\EventDispatcher\RoadRunner\RoadRunnerTaskListener;
use Spiral\RoadRunner\Jobs\JobsInterface;
use Spiral\RoadRunner\Jobs\QueueInterface;
use Spiral\RoadRunner\Jobs\Task\PreparedTaskInterface;
use Spiral\RoadRunner\Jobs\Task\QueuedTaskInterface;
use stdClass;

class RoadRunnerTaskListenerTest extends TestCase
{
    use ProphecyTrait;

    private RoadRunnerTaskListener $listener;
    private ObjectProphecy $jobs;
    private string $listenerServiceName = 'service';

    public function setUp(): void
    {
        $this->jobs = $this->prophesize(JobsInterface::class);
        $this->listener = new RoadRunnerTaskListener($this->jobs->reveal(), $this->listenerServiceName);
    }

    /**
     * @test
     * @dataProvider provideEvents
     */
    public function expectedTaskIsDispatchedBasedOnProvidedEvent(object $event, array $expectedPayload): void
    {
        $queue = $this->prophesize(QueueInterface::class);
        $task = $this->prophesize(PreparedTaskInterface::class);

        $connect = $this->jobs->connect('shlink')->willReturn($queue->reveal());
        $create = $queue->create($event::class, [
            'listenerServiceName' => $this->listenerServiceName,
            'eventPayload' => $expectedPayload,
        ])->willReturn($task->reveal());
        $dispatch = $queue->dispatch($task->reveal())->willReturn(
            $this->prophesize(QueuedTaskInterface::class)->reveal(),
        );

        ($this->listener)($event);

        $connect->shouldHaveBeenCalledOnce();
        $create->shouldHaveBeenCalledOnce();
        $dispatch->shouldHaveBeenCalledOnce();
    }

    public function provideEvents(): iterable
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
