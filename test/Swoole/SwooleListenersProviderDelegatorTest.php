<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\EventDispatcher\Swoole;

use Laminas\ServiceManager\ServiceManager;
use Mezzio\Swoole\Event\SwooleListenerProvider;
use Mezzio\Swoole\Task\DeferredServiceListenerDelegator;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
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

    /**
     * @test
     * @dataProvider provideConfigAndListeners
     */
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

    public function provideConfigAndListeners(): iterable
    {
        yield 'empty config' => [
            [],
            function (MockObject & ServiceManager $container): void {
                $container->expects($this->never())->method('addDelegator');
            },
            static function (SwooleListenerProvider $provider): void {
                Assert::assertEmpty(iterator_to_array($provider->getListenersForEvent(new stdClass())));
            },
        ];
        yield 'empty events' => [
            ['events' => []],
            function (MockObject & ServiceManager $container): void {
                $container->expects($this->never())->method('addDelegator');
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
                $container->expects($this->never())->method('addDelegator');
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
                $container->expects($this->exactly(2))->method('addDelegator')->withConsecutive(
                    ['foo', DeferredServiceListenerDelegator::class],
                    ['bar', DeferredServiceListenerDelegator::class],
                );
            },
            static function (SwooleListenerProvider $provider): void {
                Assert::assertCount(2, iterator_to_array($provider->getListenersForEvent(new stdClass())));
            },
        ];
    }
}
