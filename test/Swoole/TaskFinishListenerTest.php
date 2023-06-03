<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\EventDispatcher\Swoole;

use Mezzio\Swoole\Event\TaskFinishEvent;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Shlinkio\Shlink\EventDispatcher\Swoole\TaskFinishListener;

/** @deprecated */
class TaskFinishListenerTest extends TestCase
{
    private TaskFinishListener $listener;
    private MockObject & LoggerInterface $logger;

    public function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->listener = new TaskFinishListener($this->logger);
    }

    #[Test]
    public function loggerIsCalledWhenListenerIsInvoked(): void
    {
        $event = $this->createMock(TaskFinishEvent::class);
        $event->expects($this->once())->method('getTaskId')->willReturn(123);
        $this->logger->expects($this->once())->method('notice')->with('Task {taskId} has finished', ['taskId' => 123]);

        ($this->listener)($event);
    }
}
