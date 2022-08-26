<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\EventDispatcher\RoadRunner;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Shlinkio\Shlink\EventDispatcher\RoadRunner\RoadRunnerTaskConsumerToListener;
use Shlinkio\Shlink\EventDispatcher\Util\JsonUnserializable;
use ShlinkioTest\Shlink\EventDispatcher\Util\DummyJsonDeserializable;
use Spiral\RoadRunner\Jobs\ConsumerInterface;
use Spiral\RoadRunner\Jobs\Task\ReceivedTaskInterface;

class RoadRunnerTaskConsumerToListenerTest extends TestCase
{
    use ProphecyTrait;

    private RoadRunnerTaskConsumerToListener $taskConsumer;
    private ObjectProphecy $consumer;
    private ObjectProphecy $container;
    private ObjectProphecy $logger;

    public function setUp(): void
    {
        $this->consumer = $this->prophesize(ConsumerInterface::class);
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);

        $this->taskConsumer = new RoadRunnerTaskConsumerToListener(
            $this->consumer->reveal(),
            $this->container->reveal(),
            $this->logger->reveal(),
        );
    }

    /** @test */
    public function warningIsLoggedWhenEventIsNotDeserializable(): void
    {
        $callCount = 0;
        $task = $this->prophesize(ReceivedTaskInterface::class);
        $task->getName()->willReturn('not_deserializable');
        $waitTask = $this->consumer->waitTask()->will(function () use (&$callCount, $task) {
            $callCount++;
            return $callCount === 1 ? $task->reveal() : null;
        });

        $this->taskConsumer->listenForTasks();

        $waitTask->shouldHaveBeenCalledTimes(2);
        $this->logger->warning(
            'It was not possible to process task for event "{event}", because it does not implement {implements}',
            ['event' => 'not_deserializable', 'implements' => JsonUnserializable::class],
        )->shouldHaveBeenCalledOnce();
        $this->container->get(Argument::cetera())->shouldNotHaveBeenCalled();
        $task->complete()->shouldHaveBeenCalledOnce();
        $task->fail(Argument::cetera())->shouldNotHaveBeenCalled();
    }

    /** @test */
    public function listenerIsLoadedAndInvoked(): void
    {
        $callCount = 0;
        $task = $this->prophesize(ReceivedTaskInterface::class);
        $task->getName()->willReturn(DummyJsonDeserializable::class);
        $task->getPayload()->willReturn([
            'listenerServiceName' => 'my_listener',
            'eventPayload' => [],
        ]);
        $waitTask = $this->consumer->waitTask()->will(function () use (&$callCount, $task) {
            $callCount++;
            return $callCount === 1 ? $task->reveal() : null;
        });
        $getListener = $this->container->get('my_listener')->willReturn(function (): void {
        });

        $this->taskConsumer->listenForTasks();

        $waitTask->shouldHaveBeenCalledTimes(2);
        $this->logger->warning(Argument::cetera())->shouldNotHaveBeenCalled();
        $getListener->shouldHaveBeenCalledOnce();
        $task->complete()->shouldHaveBeenCalledOnce();
        $task->fail(Argument::cetera())->shouldNotHaveBeenCalled();
    }

    /** @test */
    public function taskIsFailedInCaseOfError(): void
    {
        $callCount = 0;
        $task = $this->prophesize(ReceivedTaskInterface::class);
        $task->getName()->willReturn(DummyJsonDeserializable::class);
        $task->getPayload()->willReturn([
            'listenerServiceName' => 'my_listener',
            'eventPayload' => [],
        ]);
        $waitTask = $this->consumer->waitTask()->will(function () use (&$callCount, $task) {
            $callCount++;
            return $callCount === 1 ? $task->reveal() : null;
        });
        $getListener = $this->container->get('my_listener')->willReturn(function (): void {
            throw new RuntimeException('error');
        });

        $this->taskConsumer->listenForTasks();

        $waitTask->shouldHaveBeenCalledTimes(2);
        $this->logger->warning(Argument::cetera())->shouldNotHaveBeenCalled();
        $getListener->shouldHaveBeenCalledOnce();
        $task->complete()->shouldNotHaveBeenCalled();
        $task->fail(Argument::type(RuntimeException::class))->shouldHaveBeenCalledOnce();
    }
}
