<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\EventDispatcher\RoadRunner;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Shlinkio\Shlink\EventDispatcher\RoadRunner\RoadRunnerTaskConsumerToListenerFactory;
use Spiral\RoadRunner\WorkerInterface;

class RoadRunnerTaskConsumerToListenerFactoryTest extends TestCase
{
    use ProphecyTrait;

    private RoadRunnerTaskConsumerToListenerFactory $factory;
    private ObjectProphecy $container;

    protected function setUp(): void
    {
        $this->factory = new RoadRunnerTaskConsumerToListenerFactory();
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    /** @test */
    public function serviceIsProperlyCreated(): void
    {
        $hasWorker = $this->container->has(WorkerInterface::class)->willReturn(true);
        $getWorker = $this->container->get(WorkerInterface::class)->willReturn(
            $this->prophesize(WorkerInterface::class)->reveal(),
        );
        $getLogger = $this->container->get(LoggerInterface::class)->willReturn(
            $this->prophesize(LoggerInterface::class)->reveal(),
        );

        ($this->factory)($this->container->reveal());

        $hasWorker->shouldHaveBeenCalledOnce();
        $getWorker->shouldHaveBeenCalledOnce();
        $getLogger->shouldHaveBeenCalledOnce();
    }
}
