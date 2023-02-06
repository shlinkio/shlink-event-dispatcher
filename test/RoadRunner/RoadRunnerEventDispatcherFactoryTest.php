<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\EventDispatcher\RoadRunner;

use League\Event\EventDispatcher;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionObject;
use Shlinkio\Shlink\EventDispatcher\RoadRunner\RoadRunnerEventDispatcherFactory;
use Spiral\RoadRunner\Jobs\Jobs;
use Spiral\RoadRunner\Jobs\JobsInterface;

use function putenv;
use function sprintf;

class RoadRunnerEventDispatcherFactoryTest extends TestCase
{
    private RoadRunnerEventDispatcherFactory $factory;
    private MockObject & ContainerInterface $container;

    protected function setUp(): void
    {
        $this->factory = new RoadRunnerEventDispatcherFactory();
        $this->container = $this->createMock(ContainerInterface::class);
        $this->container->method('get')->willReturnMap([
            ['config', [
                'events' => [
                    'async' => [
                        'foo' => ['bar', 'baz'],
                        'bar' => ['foo'],
                    ],
                ],
            ]],
            [Jobs::class, $this->createMock(JobsInterface::class)],
        ]);
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
        $dispatcher = ($this->factory)($this->container);
        $listenerProvider = $this->getPrivateProp($dispatcher, 'listenerProvider');
        $listenersPerEvent = $this->getPrivateProp($listenerProvider, 'listenersPerEvent');

        self::assertCount($amountOfListeners, $listenersPerEvent);
    }

    public static function provideEnv(): iterable
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
