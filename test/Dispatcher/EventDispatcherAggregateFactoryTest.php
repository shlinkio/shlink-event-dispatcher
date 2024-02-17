<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\EventDispatcher\Dispatcher;

use League\Event\EventDispatcher;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
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

    #[Test, DataProvider('provideConfigs')]
    public function fetchesAsyncDispatcherFromContainer(array $config): void
    {
        $this->container->expects($this->exactly(3))->method('get')->willReturnMap([
            [RoadRunnerEventDispatcherFactory::ROAD_RUNNER_DISPATCHER, $this->ed],
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
