<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\EventDispatcher\RoadRunner;

use League\Event\EventDispatcher;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionObject;
use Shlinkio\Shlink\EventDispatcher\Listener\EnabledListenerCheckerInterface;
use Shlinkio\Shlink\EventDispatcher\RoadRunner\RoadRunnerEventDispatcherFactory;
use Shlinkio\Shlink\EventDispatcher\Util\RequestIdProviderInterface;
use Spiral\RoadRunner\Jobs\Jobs;
use Spiral\RoadRunner\Jobs\JobsInterface;
use stdClass;

use function putenv;
use function sprintf;

class RoadRunnerEventDispatcherFactoryTest extends TestCase
{
    private RoadRunnerEventDispatcherFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new RoadRunnerEventDispatcherFactory();
    }

    protected function tearDown(): void
    {
        putenv('RR_MODE');
    }

    #[Test, DataProvider('provideEnv')]
    public function asyncEventsAreRegisteredOnRoadRunnerContextOnly(string $mode, int $amountOfListeners): void
    {
        putenv(sprintf('RR_MODE%s', $mode));

        $dispatcher = ($this->factory)($this->container());
        $listenerProvider = $this->getPrivateProp($dispatcher, 'listenerProvider');
        $listenersPerEvent = $this->getPrivateProp($listenerProvider, 'listenersPerEvent');

        self::assertCount($amountOfListeners, $listenersPerEvent);
    }

    public static function provideEnv(): iterable
    {
        yield 'no-rr' => ['', 0];
        yield 'rr' => ['=http', 2];
    }

    #[Test]
    #[TestWith([true])]
    #[TestWith([false])]
    public function skipsListenersWhenEnabledListenerCheckerIsRegistered(bool $hasRequestIdProvider): void
    {
        putenv('RR_MODE=http');

        $container = $this->container(new class implements EnabledListenerCheckerInterface {
            public function shouldRegisterListener(string $event, string $listener, bool $isAsync): bool
            {
                return $isAsync && $listener === 'foo';
            }
        }, hasRequestIdProvider: $hasRequestIdProvider);

        $dispatcher = ($this->factory)($container);
        $listenerProvider = $this->getPrivateProp($dispatcher, 'listenerProvider');

        Assert::assertCount(0, [...$listenerProvider->getListenersForEvent(new stdClass())]);
        Assert::assertCount(1, [...$listenerProvider->getListenersForEvent(new EventDispatcher())]);
    }

    private function getPrivateProp(object $object, string $propName): mixed
    {
        $ref = new ReflectionObject($object);
        $prop = $ref->getProperty($propName);
        $prop->setAccessible(true);

        return $prop->getValue($object);
    }

    private function container(
        EnabledListenerCheckerInterface|null $listenerChecker = null,
        bool $hasRequestIdProvider = false,
    ): ContainerInterface {
        $container = $this->createMock(ContainerInterface::class);
        $getServiceReturnMap = [
            ['config', [
                'events' => [
                    'async' => [
                        stdClass::class => ['bar', 'baz'],
                        EventDispatcher::class => ['foo'],
                    ],
                ],
            ]],
            [Jobs::class, $this->createMock(JobsInterface::class)],
        ];
        $hasServiceReturnMap = [
            [RequestIdProviderInterface::class, $hasRequestIdProvider],
        ];

        if ($listenerChecker !== null) {
            $hasServiceReturnMap[] = [EnabledListenerCheckerInterface::class, true];
            $getServiceReturnMap[] = [EnabledListenerCheckerInterface::class, $listenerChecker];
        }

        if ($hasRequestIdProvider) {
            $getServiceReturnMap[] = [
                RequestIdProviderInterface::class,
                $this->createMock(RequestIdProviderInterface::class),
            ];
        }

        $container->method('get')->willReturnMap($getServiceReturnMap);
        $container->method('has')->willReturnMap($hasServiceReturnMap);

        return $container;
    }
}
