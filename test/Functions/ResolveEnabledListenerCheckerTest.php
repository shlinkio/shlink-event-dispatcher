<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\EventDispatcher\Functions;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Shlinkio\Shlink\EventDispatcher\Listener\DummyEnabledListenerChecker;
use Shlinkio\Shlink\EventDispatcher\Listener\EnabledListenerCheckerInterface;
use stdClass;

use function get_class;
use function Shlinkio\Shlink\EventDispatcher\resolveEnabledListenerChecker;

class ResolveEnabledListenerCheckerTest extends TestCase
{
    private MockObject & ContainerInterface $container;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
    }

    /**
     * @param class-string $expectedResult
     */
    #[Test, DataProvider('provideContainerConfigs')]
    public function expectedInstanceIsReturned(callable $setUpContainer, string $expectedResult): void
    {
        $setUpContainer($this->container);
        $result = resolveEnabledListenerChecker($this->container);

        self::assertInstanceOf($expectedResult, $result);
    }

    public static function provideContainerConfigs(): iterable
    {
        $validChecker = new class implements EnabledListenerCheckerInterface {
            public function shouldRegisterListener(string $event, string $listener, bool $isAsync): bool
            {
                return false;
            }
        };

        yield 'no checker service' => [function (MockObject & ContainerInterface $container): void {
            $container->expects(self::once())->method('has')->with(EnabledListenerCheckerInterface::class)->willReturn(
                false,
            );
            $container->expects(self::never())->method('get');
        }, DummyEnabledListenerChecker::class];
        yield 'invalid checker service' => [function (MockObject & ContainerInterface $container): void {
            $container->expects(self::once())->method('has')->with(EnabledListenerCheckerInterface::class)->willReturn(
                true,
            );
            $container->expects(self::once())->method('get')->with(EnabledListenerCheckerInterface::class)->willReturn(
                new stdClass(),
            );
        }, DummyEnabledListenerChecker::class];
        yield 'valid checker service' => [
            function (MockObject & ContainerInterface $container) use ($validChecker): void {
                $container->expects(self::once())->method('has')->with(
                    EnabledListenerCheckerInterface::class,
                )->willReturn(true);
                $container->expects(self::once())->method('get')->with(
                    EnabledListenerCheckerInterface::class,
                )->willReturn($validChecker);
            },
            get_class($validChecker),
        ];
    }
}
