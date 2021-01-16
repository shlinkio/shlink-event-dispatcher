<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\EventDispatcher\Listener;

use Laminas\ServiceManager\ServiceManager;
use Mezzio\Swoole\Event\SwooleListenerProvider;
use Mezzio\Swoole\Task\DeferredServiceListenerDelegator;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Shlinkio\Shlink\EventDispatcher\Listener\AsyncListenersProviderDelegator;
use stdClass;

use function iterator_to_array;

class AsyncListenersProviderDelegatorTest extends TestCase
{
    use ProphecyTrait;

    private AsyncListenersProviderDelegator $delegator;
    private ObjectProphecy $container;

    public function setUp(): void
    {
        $this->delegator = new AsyncListenersProviderDelegator();
        $this->container = $this->prophesize(ServiceManager::class);
    }

    /**
     * @test
     * @dataProvider provideConfigAndListeners
     */
    public function expectedEventListenersAreRegistered(array $config, callable $assertListeners): void
    {
        $getConfig = $this->container->get('config')->willReturn($config);

        $provider = ($this->delegator)($this->container->reveal(), '', fn () => new SwooleListenerProvider());

        $getConfig->shouldHaveBeenCalledOnce();
        $assertListeners($provider, $this->container);
    }

    public function provideConfigAndListeners(): iterable
    {
        yield 'empty config' => [
            [],
            static function (SwooleListenerProvider $provider, ObjectProphecy $container): void {
                Assert::assertEmpty(iterator_to_array($provider->getListenersForEvent(new stdClass())));
                $container->addDelegator(Argument::cetera())->shouldNotHaveBeenCalled();
            },
        ];
        yield 'empty events' => [
            ['events' => []],
            static function (SwooleListenerProvider $provider, ObjectProphecy $container): void {
                Assert::assertEmpty(iterator_to_array($provider->getListenersForEvent(new stdClass())));
                $container->addDelegator(Argument::cetera())->shouldNotHaveBeenCalled();
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
            static function (SwooleListenerProvider $provider, ObjectProphecy $container): void {
                Assert::assertEmpty(iterator_to_array($provider->getListenersForEvent(new stdClass())));
                $container->addDelegator(Argument::cetera())->shouldNotHaveBeenCalled();
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
            static function (SwooleListenerProvider $provider, ObjectProphecy $container): void {
                Assert::assertCount(2, iterator_to_array($provider->getListenersForEvent(new stdClass())));
                $container->addDelegator('foo', DeferredServiceListenerDelegator::class)->shouldHaveBeenCalledOnce();
                $container->addDelegator('bar', DeferredServiceListenerDelegator::class)->shouldHaveBeenCalledOnce();
            },
        ];
    }
}
