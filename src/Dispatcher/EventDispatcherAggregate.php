<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\EventDispatcher\Dispatcher;

use Psr\EventDispatcher\EventDispatcherInterface;

use function array_key_exists;
use function get_class;

class EventDispatcherAggregate implements EventDispatcherInterface
{
    private EventDispatcherInterface $asyncDispatcher;
    private EventDispatcherInterface $regularDispatcher;
    private array $eventsConfig;

    public function __construct(
        EventDispatcherInterface $asyncDispatcher,
        EventDispatcherInterface $regularDispatcher,
        array $eventsConfig
    ) {
        $this->asyncDispatcher = $asyncDispatcher;
        $this->regularDispatcher = $regularDispatcher;
        $this->eventsConfig = $eventsConfig;
    }

    public function dispatch(object $event): object
    {
        if (array_key_exists(get_class($event), $this->eventsConfig['async'] ?? [])) {
            return $this->asyncDispatcher->dispatch($event);
        }

        return $this->regularDispatcher->dispatch($event);
    }
}
