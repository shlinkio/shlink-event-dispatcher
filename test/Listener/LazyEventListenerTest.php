<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\EventDispatcher\Listener;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Shlinkio\Shlink\EventDispatcher\Listener\LazyEventListener;
use stdClass;

class LazyEventListenerTest extends TestCase
{
    private LazyEventListener $listener;
    private MockObject & ContainerInterface $container;
    private string $listenerServiceName;

    public function setUp(): void
    {
        $this->listenerServiceName = 'the_listener_service_name';
        $this->container = $this->createMock(ContainerInterface::class);

        $this->listener = new LazyEventListener($this->container, $this->listenerServiceName);
    }

    /** @test */
    public function invokesServiceAsCallableWhenInvoked(): void
    {
        $isCalled = false;
        $event = null;
        $actualListener = static function (object $actualEvent) use (&$isCalled, &$event): void {
            $isCalled = true;
            $event = $actualEvent;
        };
        $this->container->expects($this->once())->method('get')->with($this->listenerServiceName)->willReturn(
            $actualListener,
        );
        $passedEvent = new stdClass();

        ($this->listener)($passedEvent);

        self::assertTrue($isCalled);
        self::assertSame($event, $passedEvent);
    }
}
