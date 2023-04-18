<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\EventDispatcher\RoadRunner;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Shlinkio\Shlink\EventDispatcher\RoadRunner\RoadRunnerTaskConsumerToListener;
use Shlinkio\Shlink\EventDispatcher\Util\JsonUnserializable;
use ShlinkioTest\Shlink\EventDispatcher\Util\DummyJsonDeserializable;
use Spiral\RoadRunner\Jobs\ConsumerInterface;
use Spiral\RoadRunner\Jobs\Task\ReceivedTaskInterface;

use function Shlinkio\Shlink\Json\json_encode;

class RoadRunnerTaskConsumerToListenerTest extends TestCase
{
    private RoadRunnerTaskConsumerToListener $taskConsumer;
    private MockObject & ConsumerInterface $consumer;
    private MockObject & ContainerInterface $container;
    private MockObject & LoggerInterface $logger;

    public function setUp(): void
    {
        $this->consumer = $this->createMock(ConsumerInterface::class);
        $this->container = $this->createMock(ContainerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->taskConsumer = new RoadRunnerTaskConsumerToListener($this->consumer, $this->container, $this->logger);
    }

    #[Test]
    public function warningIsLoggedWhenEventIsNotDeserializable(): void
    {
        $callCount = 0;
        $task = $this->createMock(ReceivedTaskInterface::class);
        $task->method('getName')->willReturn('not_deserializable');
        $task->expects($this->once())->method('complete');
        $task->expects($this->never())->method('fail');
        $this->consumer->expects($this->exactly(2))->method('waitTask')->willReturnCallback(
            function () use (&$callCount, $task) {
                $callCount++;
                return $callCount === 1 ? $task : null;
            },
        );
        $this->container->expects($this->never())->method('get');
        $this->logger->expects($this->once())->method('warning')->with(
            'It was not possible to process task for event "{event}", because it does not implement {implements}',
            ['event' => 'not_deserializable', 'implements' => JsonUnserializable::class],
        );

        $this->taskConsumer->listenForTasks();
    }

    #[Test]
    public function listenerIsLoadedAndInvoked(): void
    {
        $callCount = 0;
        $task = $this->createMock(ReceivedTaskInterface::class);
        $task->method('getName')->willReturn(DummyJsonDeserializable::class);
        $task->method('getPayload')->willReturn(json_encode([
            'listenerServiceName' => 'my_listener',
            'eventPayload' => [],
        ]));
        $task->expects($this->once())->method('complete');
        $task->expects($this->never())->method('fail');
        $this->consumer->expects($this->exactly(2))->method('waitTask')->willReturnCallback(
            function () use (&$callCount, $task) {
                $callCount++;
                return $callCount === 1 ? $task : null;
            },
        );
        $this->container->expects($this->once())->method('get')->with('my_listener')->willReturn(function (): void {
        });
        $this->logger->expects($this->never())->method('warning');

        $this->taskConsumer->listenForTasks();
    }

    #[Test]
    public function taskIsFailedInCaseOfError(): void
    {
        $callCount = 0;
        $task = $this->createMock(ReceivedTaskInterface::class);
        $task->method('getName')->willReturn(DummyJsonDeserializable::class);
        $task->method('getPayload')->willReturn(json_encode([
            'listenerServiceName' => 'my_listener',
            'eventPayload' => [],
        ]));
        $task->expects($this->never())->method('complete');
        $task->expects($this->once())->method('fail')->with($this->isInstanceOf(RuntimeException::class));
        $this->consumer->expects($this->exactly(2))->method('waitTask')->willReturnCallback(
            function () use (&$callCount, $task) {
                $callCount++;
                return $callCount === 1 ? $task : null;
            },
        );
        $this->container->expects($this->once())->method('get')->with('my_listener')->willReturn(function (): void {
            throw new RuntimeException('error');
        });
        $this->logger->expects($this->never())->method('warning');

        $this->taskConsumer->listenForTasks();
    }
}
