<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\EventDispatcher\Dispatcher;

use Override;
use Psr\EventDispatcher\EventDispatcherInterface;

use function array_keys;
use function in_array;

class EventDispatcherAggregate implements EventDispatcherInterface
{
    /** @var list<array-key> */
    private array $asyncEvents;
    /** @var list<array-key> */
    private array $regularEvents;

    public function __construct(
        private readonly EventDispatcherInterface $asyncDispatcher,
        private readonly EventDispatcherInterface $regularDispatcher,
        array $eventsConfig,
    ) {
        /** @var array<string, string[]> $asyncEvents */
        $asyncEvents = $eventsConfig['async'] ?? [];
        /** @var array<string, string[]> $regularEvents */
        $regularEvents = $eventsConfig['regular'] ?? [];

        $this->asyncEvents = array_keys($asyncEvents);
        $this->regularEvents = array_keys($regularEvents);
    }

    #[Override]
    public function dispatch(object $event): object
    {
        $initialEventClass = $event::class;

        if (in_array($initialEventClass, $this->regularEvents, strict: true)) {
            $event = $this->regularDispatcher->dispatch($event);
        }

        if (in_array($initialEventClass, $this->asyncEvents, strict: true)) {
            $event = $this->asyncDispatcher->dispatch($event);
        }

        return $event;
    }
}
