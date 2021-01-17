<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\EventDispatcher\Dispatcher;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\EventDispatcher\EventDispatcherInterface;
use Shlinkio\Shlink\EventDispatcher\Dispatcher\EventDispatcherAggregate;
use stdClass;

class EventDispatcherAggregateTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $asyncDispatcher;
    private ObjectProphecy $regularDispatcher;

    public function setUp(): void
    {
        $this->asyncDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->regularDispatcher = $this->prophesize(EventDispatcherInterface::class);
    }

    /**
     * @test
     * @dataProvider provideEventsConfigs
     */
    public function expectedDispatcherIsInvoked(array $eventsConfig, int $asyncCalls, int $regularCalls): void
    {
        $event = new stdClass();
        $asyncDispatch = $this->asyncDispatcher->dispatch($event)->willReturn($event);
        $regularDispatch = $this->regularDispatcher->dispatch($event)->willReturn($event);
        $dispatcher = new EventDispatcherAggregate(
            $this->asyncDispatcher->reveal(),
            $this->regularDispatcher->reveal(),
            $eventsConfig,
        );

        $dispatcher->dispatch($event);

        $asyncDispatch->shouldHaveBeenCalledTimes($asyncCalls);
        $regularDispatch->shouldHaveBeenCalledTimes($regularCalls);
    }

    public function provideEventsConfigs(): iterable
    {
        yield 'no async events' => [[], 0, 1];
        yield 'async events' => [
            ['async' => [
                stdClass::class => [],
            ]],
            1,
            0,
        ];
        yield 'async events with fallback' => [
            [
                'async' => [
                    stdClass::class => [],
                ],
                'fallback_async_to_regular' => true,
            ],
            0,
            1,
        ];
    }
}
