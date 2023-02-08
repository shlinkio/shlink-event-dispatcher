<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\EventDispatcher\Swoole;

use Laminas\ServiceManager\ServiceManager;
use Mezzio\Swoole\Event\SwooleListenerProvider;
use Mezzio\Swoole\Task\DeferredServiceListenerDelegator;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use PHPUnit\Framework\TestCase;
use Shlinkio\Shlink\EventDispatcher\Swoole\SwooleListenersProviderDelegator;
use stdClass;

use function iterator_to_array;

class SwooleListenersProviderDelegatorTest extends TestCase
{
    private SwooleListenersProviderDelegator $delegator;
    private MockObject & ServiceManager $container;

    public function setUp(): void
    {
        $this->delegator = new SwooleListenersProviderDelegator();
        $this->container = $this->createMock(ServiceManager::class);
    }

    #[Test, DataProvider('provideConfigAndListeners')]
    public function expectedEventListenersAreRegistered(
        array $config,
        callable $setUp,
        callable $assertListeners,
    ): void {
        $this->container->expects($this->once())->method('get')->with('config')->willReturn($config);
        $setUp($this->container);

        $provider = ($this->delegator)($this->container, '', fn () => new SwooleListenerProvider());

        $assertListeners($provider);
    }

    public static function provideConfigAndListeners(): iterable
    {
        yield 'empty config' => [
            [],
            function (MockObject & ServiceManager $container): void {
                $container->expects(new InvokedCount(0))->method('addDelegator');
            },
            static function (SwooleListenerProvider $provider): void {
                Assert::assertEmpty(iterator_to_array($provider->getListenersForEvent(new stdClass())));
            },
        ];
        yield 'empty events' => [
            ['events' => []],
            function (MockObject & ServiceManager $container): void {
                $container->expects(new InvokedCount(0))->method('addDelegator');
            },
            static function (SwooleListenerProvider $provider): void {
                Assert::assertEmpty(iterator_to_array($provider->getListenersForEvent(new stdClass())));
            },
        ];
        yield 'no async events' => [
            ['events' => [
                'regular' => [
                    stdClass::class => [
                        'foo',
                        'bar',
                    ],
                ],
            ]],
            function (MockObject & ServiceManager $container): void {
                $container->expects(new InvokedCount(0))->method('addDelegator');
            },
            static function (SwooleListenerProvider $provider): void {
                Assert::assertEmpty(iterator_to_array($provider->getListenersForEvent(new stdClass())));
            },
        ];
        yield 'async events' => [
            ['events' => [
                'async' => [
                    stdClass::class => [
                        'foo',
                        'bar',
                    ],
                ],
            ]],
            function (MockObject & ServiceManager $container): void {
                $callCount = 0;
                $container->expects(new InvokedCount(2))->method('addDelegator')->willReturnCallback(
                    function (string $name, string $factory) use (&$callCount): void {
                        Assert::assertEquals($callCount === 0 ? 'foo' : 'bar', $name);
                        Assert::assertEquals(DeferredServiceListenerDelegator::class, $factory);

                        $callCount++;
                    },
                );
            },
            static function (SwooleListenerProvider $provider): void {
                Assert::assertCount(2, iterator_to_array($provider->getListenersForEvent(new stdClass())));
            },
        ];
    }
}
