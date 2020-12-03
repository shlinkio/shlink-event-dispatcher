<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\EventDispatcher\Listener;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Shlinkio\Shlink\EventDispatcher\Listener\LazyEventListener;
use stdClass;

class LazyEventListenerTest extends TestCase
{
    use ProphecyTrait;

    private LazyEventListener $listener;
    private ObjectProphecy $container;
    private string $listenerServiceName;

    public function setUp(): void
    {
        $this->listenerServiceName = 'the_listener_service_name';
        $this->container = $this->prophesize(ContainerInterface::class);

        $this->listener = new LazyEventListener($this->container->reveal(), $this->listenerServiceName);
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
        $getListener = $this->container->get($this->listenerServiceName)->willReturn($actualListener);
        $passedEvent = new stdClass();

        ($this->listener)($passedEvent);

        self::assertTrue($isCalled);
        self::assertSame($event, $passedEvent);
        $getListener->shouldHaveBeenCalledOnce();
    }
}
