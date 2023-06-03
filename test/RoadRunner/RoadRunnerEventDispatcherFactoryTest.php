<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\EventDispatcher\RoadRunner;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionObject;
use Shlinkio\Shlink\EventDispatcher\Listener\EnabledListenerCheckerInterface;
use Shlinkio\Shlink\EventDispatcher\RoadRunner\RoadRunnerEventDispatcherFactory;
use Spiral\RoadRunner\Jobs\Jobs;
use Spiral\RoadRunner\Jobs\JobsInterface;
use stdClass;
use Symfony\Contracts\EventDispatcher\Event;

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
    public function skipsListenersWhenEnabledListenerCheckerIsRegistered(): void
    {
        putenv('RR_MODE=http');

        $container = $this->container(new class implements EnabledListenerCheckerInterface {
            public function shouldRegisterListener(string $event, string $listener, bool $isAsync): bool
            {
                return $isAsync && $listener === 'foo';
            }
        });

        $dispatcher = ($this->factory)($container);
        $listenerProvider = $this->getPrivateProp($dispatcher, 'listenerProvider');

        Assert::assertCount(0, [...$listenerProvider->getListenersForEvent(new stdClass())]);
        Assert::assertCount(1, [...$listenerProvider->getListenersForEvent(new Event())]);
    }

    private function getPrivateProp(object $object, string $propName): mixed
    {
        $ref = new ReflectionObject($object);
        $prop = $ref->getProperty($propName);
        $prop->setAccessible(true);

        return $prop->getValue($object);
    }

    private function container(?EnabledListenerCheckerInterface $listenerChecker = null): ContainerInterface
    {
        $container = $this->createMock(ContainerInterface::class);

        $getServiceReturnMap = [
            ['config', [
                'events' => [
                    'async' => [
                        stdClass::class => ['bar', 'baz'],
                        Event::class => ['foo'],
                    ],
                ],
            ]],
            [Jobs::class, $this->createMock(JobsInterface::class)],
        ];
        if ($listenerChecker !== null) {
            $container->method('has')->with(EnabledListenerCheckerInterface::class)->willReturn(true);
            $getServiceReturnMap[] = [EnabledListenerCheckerInterface::class, $listenerChecker];
        }

        $container->method('get')->willReturnMap($getServiceReturnMap);

        return $container;
    }
}
