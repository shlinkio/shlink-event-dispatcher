<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\EventDispatcher\Dispatcher;

use Psr\EventDispatcher\EventDispatcherInterface;

use function array_keys;
use function get_class;
use function in_array;

class EventDispatcherAggregate implements EventDispatcherInterface
{
    private EventDispatcherInterface $asyncDispatcher;
    private EventDispatcherInterface $regularDispatcher;
    private array $asyncEvents;
    private bool $fallbackAsync;

    public function __construct(
        EventDispatcherInterface $asyncDispatcher,
        EventDispatcherInterface $regularDispatcher,
        array $eventsConfig
    ) {
        $this->asyncDispatcher = $asyncDispatcher;
        $this->regularDispatcher = $regularDispatcher;
        $this->asyncEvents = array_keys($eventsConfig['async'] ?? []);
        $this->fallbackAsync = (bool) ($eventsConfig['fallback_async_to_regular'] ?? false);
    }

    public function dispatch(object $event): object
    {
        if (!$this->fallbackAsync && in_array(get_class($event), $this->asyncEvents, true)) {
            return $this->asyncDispatcher->dispatch($event);
        }

        return $this->regularDispatcher->dispatch($event);
    }
}
