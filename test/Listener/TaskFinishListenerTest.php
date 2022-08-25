<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\EventDispatcher\Listener;

use Mezzio\Swoole\Event\TaskFinishEvent;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Shlinkio\Shlink\EventDispatcher\Swoole\TaskFinishListener;

class TaskFinishListenerTest extends TestCase
{
    use ProphecyTrait;

    private TaskFinishListener $listener;
    private ObjectProphecy $logger;

    public function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->listener = new TaskFinishListener($this->logger->reveal());
    }

    /** @test */
    public function loggerIsCalledWhenListenerIsInvoked(): void
    {
        $event = $this->prophesize(TaskFinishEvent::class);
        $getTaskId = $event->getTaskId()->willReturn(123);

        ($this->listener)($event->reveal());

        $getTaskId->shouldHaveBeenCalledOnce();
        $this->logger->notice('Task {taskId} has finished', ['taskId' => 123])->shouldHaveBeenCalledOnce();
    }
}
