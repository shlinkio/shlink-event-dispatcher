<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\EventDispatcher\Dispatcher;

use League\Event\EventDispatcher;
use Mezzio\Swoole\Event\EventDispatcherInterface as SwooleEventDispatcherInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Shlinkio\Shlink\EventDispatcher\Dispatcher\EventDispatcherAggregateFactory;
use Shlinkio\Shlink\EventDispatcher\Dispatcher\SyncEventDispatcherFactory;
use Shlinkio\Shlink\EventDispatcher\RoadRunner\RoadRunnerEventDispatcherFactory;

class EventDispatcherAggregateFactoryTest extends TestCase
{
    private EventDispatcherAggregateFactory $factory;
    private EventDispatcherInterface $ed;
    private MockObject & ContainerInterface $container;

    public function setUp(): void
    {
        $this->factory = new EventDispatcherAggregateFactory();
        $this->container = $this->createMock(ContainerInterface::class);
        $this->ed = new EventDispatcher();
    }

    /**
     * @test
     * @dataProvider provideConfigs
     */
    public function createsAsyncDispatcherWhenNotFoundInContainer(array $config): void
    {
        $this->container->expects($this->once())->method('has')->with(
            SwooleEventDispatcherInterface::class,
        )->willReturn(false);
        $this->container->expects($this->exactly(3))->method('get')->willReturnMap([
            [RoadRunnerEventDispatcherFactory::ROAD_RUNNER_DISPATCHER, $this->ed],
            [SyncEventDispatcherFactory::SYNC_DISPATCHER, $this->ed],
            ['config', $config],
        ]);

        ($this->factory)($this->container);
    }

    /**
     * @test
     * @dataProvider provideConfigs
     */
    public function fetchesAsyncDispatcherFromContainerWhenFound(array $config): void
    {
        $this->container->expects($this->once())->method('has')->with(
            SwooleEventDispatcherInterface::class,
        )->willReturn(true);
        $this->container->expects($this->exactly(3))->method('get')->willReturnMap([
            [SwooleEventDispatcherInterface::class, $this->ed],
            [SyncEventDispatcherFactory::SYNC_DISPATCHER, $this->ed],
            ['config', $config],
        ]);

        ($this->factory)($this->container);
    }

    public static function provideConfigs(): iterable
    {
        yield 'empty config' => [[]];
        yield 'non-empty config' => [['events' => []]];
    }
}
