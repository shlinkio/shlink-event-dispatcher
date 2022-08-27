<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\EventDispatcher\RoadRunner;

use League\Event\EventDispatcher;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use ReflectionObject;
use Shlinkio\Shlink\EventDispatcher\RoadRunner\RoadRunnerEventDispatcherFactory;
use Spiral\RoadRunner\Jobs\Jobs;
use Spiral\RoadRunner\Jobs\JobsInterface;

use function putenv;
use function sprintf;

class RoadRunnerEventDispatcherFactoryTest extends TestCase
{
    use ProphecyTrait;

    private RoadRunnerEventDispatcherFactory $factory;
    private ObjectProphecy $container;

    protected function setUp(): void
    {
        $this->factory = new RoadRunnerEventDispatcherFactory();
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->container->get('config')->willReturn([
            'events' => [
                'async' => [
                    'foo' => ['bar', 'baz'],
                    'bar' => ['foo'],
                ],
            ],
        ]);
        $this->container->get(Jobs::class)->willReturn($this->prophesize(JobsInterface::class)->reveal());
    }

    protected function tearDown(): void
    {
        putenv('RR_MODE');
    }

    /**
     * @test
     * @dataProvider provideEnv
     */
    public function asyncEventsAreRegisteredOnRoadRunnerContextOnly(string $mode, int $amountOfListeners): void
    {
        putenv(sprintf('RR_MODE%s', $mode));

        /** @var EventDispatcher $dispatcher */
        $dispatcher = ($this->factory)($this->container->reveal());
        $listenerProvider = $this->getPrivateProp($dispatcher, 'listenerProvider');
        $listenersPerEvent = $this->getPrivateProp($listenerProvider, 'listenersPerEvent');

        self::assertCount($amountOfListeners, $listenersPerEvent);
    }

    public function provideEnv(): iterable
    {
        yield 'no-rr' => ['', 0];
        yield 'rr' => ['=http', 2];
    }

    private function getPrivateProp(object $object, string $propName): mixed
    {
        $ref = new ReflectionObject($object);
        $prop = $ref->getProperty($propName);
        $prop->setAccessible(true);

        return $prop->getValue($object);
    }
}
