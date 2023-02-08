<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\EventDispatcher\Dispatcher;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use ReflectionObject;
use Shlinkio\Shlink\EventDispatcher\Dispatcher\SyncEventDispatcherFactory;
use stdClass;
use Symfony\Contracts\EventDispatcher\Event;

class SyncEventDispatcherFactoryTest extends TestCase
{
    private SyncEventDispatcherFactory $factory;
    private MockObject & ContainerInterface $container;

    public function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->factory = new SyncEventDispatcherFactory();
    }

    #[Test, DataProvider('provideListeners')]
    public function expectedListenersAreRegistered(array $config, callable $assertListeners): void
    {
        $this->container->expects($this->once())->method('get')->with('config')->willReturn($config);

        $dispatcher = ($this->factory)($this->container);

        $ref = new ReflectionObject($dispatcher);
        $prop = $ref->getProperty('listenerProvider');
        $prop->setAccessible(true);
        $provider = $prop->getValue($dispatcher);

        $assertListeners($provider);
    }

    public static function provideListeners(): iterable
    {
        yield 'empty config' => [
            [],
            static function (ListenerProviderInterface $provider): void {
                Assert::assertEmpty([...$provider->getListenersForEvent(new stdClass())]);
            },
        ];
        yield 'empty events' => [
            ['events' => []],
            static function (ListenerProviderInterface $provider): void {
                Assert::assertEmpty([...$provider->getListenersForEvent(new stdClass())]);
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
            static function (ListenerProviderInterface $provider): void {
                Assert::assertEmpty([...$provider->getListenersForEvent(new stdClass())]);
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
            static function (ListenerProviderInterface $provider): void {
                Assert::assertCount(2, [...$provider->getListenersForEvent(new stdClass())]);
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
            static function (ListenerProviderInterface $provider): void {
                Assert::assertCount(2, [...$provider->getListenersForEvent(new stdClass())]);
                Assert::assertEmpty([...$provider->getListenersForEvent(new Event())]);
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
            static function (ListenerProviderInterface $provider): void {
                Assert::assertCount(2, [...$provider->getListenersForEvent(new stdClass())]);
                Assert::assertCount(3, [...$provider->getListenersForEvent(new Event())]);
            },
        ];
    }
}
