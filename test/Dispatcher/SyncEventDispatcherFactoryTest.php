<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\EventDispatcher\Dispatcher;

use Mezzio\Swoole\Event\SwooleListenerProvider;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use ReflectionObject;
use Shlinkio\Shlink\EventDispatcher\Dispatcher\SyncEventDispatcherFactory;
use stdClass;
use Symfony\Contracts\EventDispatcher\Event;

use function iterator_to_array;

class SyncEventDispatcherFactoryTest extends TestCase
{
    use ProphecyTrait;

    private SyncEventDispatcherFactory $factory;
    private ObjectProphecy $container;

    public function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->factory = new SyncEventDispatcherFactory();
    }

    /**
     * @test
     * @dataProvider provideListeners
     */
    public function expectedListenersAreRegistered(array $config, callable $assertListeners): void
    {
        $getConfig = $this->container->get('config')->willReturn($config);

        $dispatcher = ($this->factory)($this->container->reveal());

        $ref = new ReflectionObject($dispatcher);
        $prop = $ref->getProperty('listenerProvider');
        $prop->setAccessible(true);
        $provider = $prop->getValue($dispatcher);

        $getConfig->shouldHaveBeenCalledOnce();
        $assertListeners($provider);
    }

    public function provideListeners(): iterable
    {
        yield 'empty config' => [
            [],
            static function (SwooleListenerProvider $provider): void {
                Assert::assertEmpty(iterator_to_array($provider->getListenersForEvent(new stdClass())));
            },
        ];
        yield 'empty events' => [
            ['events' => []],
            static function (SwooleListenerProvider $provider): void {
                Assert::assertEmpty(iterator_to_array($provider->getListenersForEvent(new stdClass())));
            },
        ];
        yield 'empty regular events' => [
            ['events' => [
                'async' => [
                    stdClass::class => [
                        'foo',
                        'bar',
                    ],
                ],
            ]],
            static function (SwooleListenerProvider $provider): void {
                Assert::assertEmpty(iterator_to_array($provider->getListenersForEvent(new stdClass())));
            },
        ];
        yield 'non-empty regular events' => [
            ['events' => [
                'regular' => [
                    stdClass::class => [
                        'foo',
                        'bar',
                    ],
                ],
            ]],
            static function (SwooleListenerProvider $provider): void {
                Assert::assertCount(2, iterator_to_array($provider->getListenersForEvent(new stdClass())));
            },
        ];
        yield 'non-empty regular events and async' => [
            ['events' => [
                'regular' => [
                    stdClass::class => [
                        'foo',
                        'bar',
                    ],
                ],
                'async' => [
                    Event::class => [
                        'foo',
                        'bar',
                        'baz',
                    ],
                ],
            ]],
            static function (SwooleListenerProvider $provider): void {
                Assert::assertCount(2, iterator_to_array($provider->getListenersForEvent(new stdClass())));
                Assert::assertEmpty(iterator_to_array($provider->getListenersForEvent(new Event())));
            },
        ];
        yield 'non-empty regular events and async with fallback' => [
            ['events' => [
                'regular' => [
                    stdClass::class => [
                        'foo',
                        'bar',
                    ],
                ],
                'async' => [
                    Event::class => [
                        'foo',
                        'bar',
                        'baz',
                    ],
                ],
                'fallback_async_to_regular' => true,
            ]],
            static function (SwooleListenerProvider $provider): void {
                Assert::assertCount(2, iterator_to_array($provider->getListenersForEvent(new stdClass())));
                Assert::assertCount(3, iterator_to_array($provider->getListenersForEvent(new Event())));
            },
        ];
    }
}
