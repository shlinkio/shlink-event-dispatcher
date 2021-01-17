<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\EventDispatcher\Dispatcher;

use Mezzio\Swoole\Event\EventDispatcherInterface as SwooleEventDispatcherInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Shlinkio\Shlink\EventDispatcher\Dispatcher\EventDispatcherAggregateFactory;
use Shlinkio\Shlink\EventDispatcher\Dispatcher\SyncEventDispatcherFactory;

class EventDispatcherAggregateFactoryTest extends TestCase
{
    use ProphecyTrait;

    private EventDispatcherAggregateFactory $factory;
    private ObjectProphecy $container;

    public function setUp(): void
    {
        $this->factory = new EventDispatcherAggregateFactory();
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    /**
     * @test
     * @dataProvider provideConfigs
     */
    public function createsAsyncDispatcherWhenNotFoundInContainer(array $config): void
    {
        $hasAsyncDispatcher = $this->container->has(SwooleEventDispatcherInterface::class)->willReturn(false);
        $getAsyncDispatcher = $this->container->get(SwooleEventDispatcherInterface::class);
        $getRegularDispatcher = $this->container->get(SyncEventDispatcherFactory::SYNC_DISPATCHER)->willReturn(
            $this->prophesize(EventDispatcherInterface::class)->reveal(),
        );
        $getConfig = $this->container->get('config')->willReturn($config);

        ($this->factory)($this->container->reveal());

        $hasAsyncDispatcher->shouldHaveBeenCalledOnce();
        $getAsyncDispatcher->shouldNotHaveBeenCalled();
        $getRegularDispatcher->shouldHaveBeenCalledOnce();
        $getConfig->shouldHaveBeenCalledOnce();
    }

    /**
     * @test
     * @dataProvider provideConfigs
     */
    public function fetchesAsyncDispatcherFromContainerWhenFound(array $config): void
    {
        $dispatcherMock = $this->prophesize(EventDispatcherInterface::class)->reveal();

        $hasAsyncDispatcher = $this->container->has(SwooleEventDispatcherInterface::class)->willReturn(true);
        $getAsyncDispatcher = $this->container->get(SwooleEventDispatcherInterface::class)->willReturn(
            $dispatcherMock,
        );
        $getRegularDispatcher = $this->container->get(SyncEventDispatcherFactory::SYNC_DISPATCHER)->willReturn(
            $dispatcherMock,
        );
        $getConfig = $this->container->get('config')->willReturn($config);

        ($this->factory)($this->container->reveal());

        $hasAsyncDispatcher->shouldHaveBeenCalledOnce();
        $getAsyncDispatcher->shouldHaveBeenCalledOnce();
        $getRegularDispatcher->shouldHaveBeenCalledOnce();
        $getConfig->shouldHaveBeenCalledOnce();
    }

    public function provideConfigs(): iterable
    {
        yield 'empty config' => [[]];
        yield 'non-empty config' => [['events' => []]];
    }
}
