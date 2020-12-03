<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\EventDispatcher\Listener;

use League\Event\PrioritizedListenerRegistry;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use ReflectionObject;
use Shlinkio\Shlink\EventDispatcher\Listener\ListenerProviderFactory;
use Swoole\Http\Server as HttpServer;

use function Shlinkio\Shlink\EventDispatcher\asyncListener;
use function Shlinkio\Shlink\EventDispatcher\lazyListener;

class ListenerProviderFactoryTest extends TestCase
{
    use ProphecyTrait;

    private ListenerProviderFactory $factory;

    public function setUp(): void
    {
        $this->factory = new ListenerProviderFactory();
    }

    /**
     * @test
     * @dataProvider provideContainersWithoutEvents
     */
    public function noListenersAreAttachedWhenNoConfigOrEventsAreRegistered(ContainerInterface $container): void
    {
        $provider = ($this->factory)($container);
        $listeners = $this->getListenersFromProvider($provider);

        self::assertInstanceOf(PrioritizedListenerRegistry::class, $provider);
        self::assertEmpty($listeners);
    }

    public function provideContainersWithoutEvents(): iterable
    {
        yield 'no config' => [(function () {
            $container = $this->prophesize(ContainerInterface::class);
            $container->has('config')->willReturn(false);

            return $container->reveal();
        })()];
        yield 'no events' => [(function () {
            $container = $this->prophesize(ContainerInterface::class);
            $container->has('config')->willReturn(true);
            $container->get('config')->willReturn([]);

            return $container->reveal();
        })()];
    }

    /** @test */
    public function configuredRegularEventsAreProperlyAttached(): void
    {
        $containerMock = $this->prophesize(ContainerInterface::class);
        $containerMock->has('config')->willReturn(true);
        $containerMock->get('config')->willReturn([
            'events' => [
                'regular' => [
                    'foo' => [
                        'bar',
                        'baz',
                    ],
                    'something' => [
                        'some_listener',
                        'another_listener',
                        'foobar',
                    ],
                ],
            ],
        ]);
        $container = $containerMock->reveal();

        $provider = ($this->factory)($container);
        $listeners = $this->getListenersFromProvider($provider);

        self::assertInstanceOf(PrioritizedListenerRegistry::class, $provider);
        self::assertEquals([
            'foo' => [
                lazyListener($container, 'bar'),
                lazyListener($container, 'baz'),
            ],
            'something' => [
                lazyListener($container, 'some_listener'),
                lazyListener($container, 'another_listener'),
                lazyListener($container, 'foobar'),
            ],
        ], $listeners);
    }

    /** @test */
    public function configuredAsyncEventsAreProperlyAttached(): void
    {
        $server = $this->createMock(HttpServer::class); // Some weird errors are thrown if prophesize is used

        $containerMock = $this->prophesize(ContainerInterface::class);
        $containerMock->has('config')->willReturn(true);
        $containerMock->get('config')->willReturn([
            'events' => [
                'async' => [
                    'foo' => [
                        'bar',
                        'baz',
                    ],
                    'something' => [
                        'some_listener',
                        'another_listener',
                        'foobar',
                    ],
                ],
            ],
        ]);
        $containerMock->has(HttpServer::class)->willReturn(true);
        $containerMock->get(HttpServer::class)->willReturn($server);
        $container = $containerMock->reveal();

        $provider = ($this->factory)($container);
        $listeners = $this->getListenersFromProvider($provider);

        self::assertInstanceOf(PrioritizedListenerRegistry::class, $provider);
        self::assertEquals([
            'foo' => [
                asyncListener($server, 'bar'),
                asyncListener($server, 'baz'),
            ],
            'something' => [
                asyncListener($server, 'some_listener'),
                asyncListener($server, 'another_listener'),
                asyncListener($server, 'foobar'),
            ],
        ], $listeners);
    }

    /**
     * @test
     * @dataProvider provideFalsyFallbackAsync
     */
    public function ignoresAsyncEventsWhenServerIsNotRegistered(?bool $fallbackAsyncToRegular): void
    {
        $containerMock = $this->prophesize(ContainerInterface::class);
        $containerMock->has('config')->willReturn(true);
        $containerMock->get('config')->willReturn([
            'events' => [
                'fallback_async_to_regular' => $fallbackAsyncToRegular,
                'async' => [
                    'foo' => [
                        'bar',
                        'baz',
                    ],
                    'something' => [
                        'some_listener',
                        'another_listener',
                        'foobar',
                    ],
                ],
            ],
        ]);
        $containerMock->has(HttpServer::class)->willReturn(false);
        $container = $containerMock->reveal();

        $provider = ($this->factory)($container);
        $listeners = $this->getListenersFromProvider($provider);

        self::assertInstanceOf(PrioritizedListenerRegistry::class, $provider);
        self::assertEmpty($listeners);
    }

    public function provideFalsyFallbackAsync(): iterable
    {
        return [[null], [false]];
    }

    /**
     * @param mixed $fallbackAsyncToRegular
     * @test
     * @dataProvider provideTruthyFallbackAsync
     */
    public function configuresAsyncEventsAsRegularWhenSetToFallback($fallbackAsyncToRegular): void
    {
        $server = $this->createMock(HttpServer::class); // Some weird errors are thrown if prophesize is used

        $containerMock = $this->prophesize(ContainerInterface::class);
        $containerMock->has('config')->willReturn(true);
        $containerMock->get('config')->willReturn([
            'events' => [
                'fallback_async_to_regular' => $fallbackAsyncToRegular,
                'async' => [
                    'foo' => [
                        'bar',
                        'baz',
                    ],
                    'something' => [
                        'some_listener',
                        'another_listener',
                        'foobar',
                    ],
                ],
            ],
        ]);
        $containerMock->has(HttpServer::class)->willReturn(true);
        $containerMock->get(HttpServer::class)->willReturn($server);
        $container = $containerMock->reveal();

        $provider = ($this->factory)($container);
        $listeners = $this->getListenersFromProvider($provider);

        self::assertInstanceOf(PrioritizedListenerRegistry::class, $provider);
        self::assertEquals([
            'foo' => [
                lazyListener($container, 'bar'),
                lazyListener($container, 'baz'),
            ],
            'something' => [
                lazyListener($container, 'some_listener'),
                lazyListener($container, 'another_listener'),
                lazyListener($container, 'foobar'),
            ],
        ], $listeners);
    }

    public function provideTruthyFallbackAsync(): iterable
    {
        return [[true], ['true'], ['something']];
    }

    private function getListenersFromProvider(ListenerProviderInterface $provider): array
    {
        $ref = new ReflectionObject($provider);
        $prop = $ref->getProperty('listenersPerEvent');
        $prop->setAccessible(true);

        $queues = $prop->getValue($provider);
        $result = [];

        foreach ($queues as $eventName => $queue) {
            $result[$eventName] = $queue->getListeners();
        }

        return $result;
    }
}
