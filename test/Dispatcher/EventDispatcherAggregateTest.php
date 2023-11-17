<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\EventDispatcher\Dispatcher;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Shlinkio\Shlink\EventDispatcher\Dispatcher\EventDispatcherAggregate;
use stdClass;

class EventDispatcherAggregateTest extends TestCase
{
    private MockObject & EventDispatcherInterface $asyncDispatcher;
    private MockObject & EventDispatcherInterface $regularDispatcher;

    public function setUp(): void
    {
        $this->asyncDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->regularDispatcher = $this->createMock(EventDispatcherInterface::class);
    }

    #[Test, DataProvider('provideEventsConfigs')]
    public function expectedDispatcherIsInvoked(array $eventsConfig, int $asyncCalls, int $regularCalls): void
    {
        $event = new stdClass();
        $this->asyncDispatcher->expects($this->exactly($asyncCalls))->method('dispatch')->with($event)->willReturn(
            $event,
        );
        $this->regularDispatcher->expects($this->exactly($regularCalls))->method('dispatch')->with($event)->willReturn(
            $event,
        );
        $dispatcher = new EventDispatcherAggregate(
            $this->asyncDispatcher,
            $this->regularDispatcher,
            $eventsConfig,
        );

        $dispatcher->dispatch($event);
    }

    public static function provideEventsConfigs(): iterable
    {
        yield 'no events' => [[], 0, 0];
        yield 'async events' => [
            ['async' => [
                stdClass::class => [],
            ]],
            1,
            0,
        ];
        yield 'regular events' => [
            ['regular' => [
                stdClass::class => [],
            ]],
            0,
            1,
        ];
        yield 'both events' => [
            [
                'regular' => [
                    stdClass::class => [],
                ],
                'async' => [
                    stdClass::class => [],
                ],
            ],
            1,
            1,
        ];
    }
}
