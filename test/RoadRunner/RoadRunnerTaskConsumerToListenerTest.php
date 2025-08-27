<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\EventDispatcher\RoadRunner;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Shlinkio\Shlink\EventDispatcher\RoadRunner\RoadRunnerTaskConsumerToListener;
use Shlinkio\Shlink\EventDispatcher\Util\JsonUnserializable;
use ShlinkioTest\Shlink\EventDispatcher\Util\DummyJsonDeserializable;
use Spiral\RoadRunner\Jobs\ConsumerInterface;
use Spiral\RoadRunner\Jobs\Task\ReceivedTask;

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
        $task = $this->createMock(ReceivedTask::class);
        $task->method('getName')->willReturn('not_deserializable');
        $task->expects($this->once())->method('ack');
        $task->expects($this->never())->method('nack');
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
    #[TestWith(['123'])]
    #[TestWith(['456'])]
    #[TestWith(['abc'])]
    public function listenerIsLoadedAndInvoked(string $requestId): void
    {
        $callCount = 0;
        $task = $this->createMock(ReceivedTask::class);
        $task->method('getName')->willReturn(DummyJsonDeserializable::class);
        $task->method('getPayload')->willReturn(json_encode([
            'listenerServiceName' => 'my_listener',
            'eventPayload' => [],
            'requestId' => $requestId,
        ]));
        $task->expects($this->once())->method('ack');
        $task->expects($this->never())->method('nack');
        $this->consumer->expects($this->exactly(2))->method('waitTask')->willReturnCallback(
            function () use (&$callCount, $task) {
                $callCount++;
                return $callCount === 1 ? $task : null;
            },
        );
        $this->container->expects($this->once())->method('get')->with('my_listener')->willReturn(function (): void {
        });
        $this->logger->expects($this->never())->method('warning');

        $providedRequestId = null;
        $this->taskConsumer->listenForTasks(function (string $id) use (&$providedRequestId): void {
            $providedRequestId = $id;
        });

        self::assertEquals($requestId, $providedRequestId);
    }

    #[Test]
    public function taskIsFailedInCaseOfError(): void
    {
        $callCount = 0;
        $task = $this->createMock(ReceivedTask::class);
        $task->method('getName')->willReturn(DummyJsonDeserializable::class);
        $task->method('getPayload')->willReturn(json_encode([
            'listenerServiceName' => 'my_listener',
            'eventPayload' => [],
            'requestId' => '123',
        ]));
        $task->expects($this->never())->method('ack');
        $task->expects($this->once())->method('nack')->with($this->isInstanceOf(RuntimeException::class));
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
