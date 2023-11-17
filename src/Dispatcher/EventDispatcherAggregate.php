<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\EventDispatcher\Dispatcher;

use Psr\EventDispatcher\EventDispatcherInterface;

use function array_keys;
use function in_array;

class EventDispatcherAggregate implements EventDispatcherInterface
{
    private array $asyncEvents;
    private array $regularEvents;

    public function __construct(
        private readonly EventDispatcherInterface $asyncDispatcher,
        private readonly EventDispatcherInterface $regularDispatcher,
        array $eventsConfig,
    ) {
        $this->asyncEvents = array_keys($eventsConfig['async'] ?? []);
        $this->regularEvents = array_keys($eventsConfig['regular'] ?? []);
    }

    public function dispatch(object $event): object
    {
        $initialEventClass = $event::class;

        if (in_array($initialEventClass, $this->regularEvents, true)) {
            $event = $this->regularDispatcher->dispatch($event);
        }

        if (in_array($initialEventClass, $this->asyncEvents, true)) {
            $event = $this->asyncDispatcher->dispatch($event);
        }

        return $event;
    }
}
