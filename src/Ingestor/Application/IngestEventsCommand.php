<?php

declare(strict_types=1);

namespace Ingestor\Application;

use Ingestor\Application\DTO\Event;
use Shared\Application\Bus\Command\Command;

final class IngestEventsCommand implements Command
{
    /**
     * @param Event[] $events
     */
    public function __construct(
        public readonly array $events
    ) {
        $this->validate();
    }

    public static function fromRawData(array $rawEvents): self
    {
        $events = array_map(
            static fn(array $rawEvent) => Event::fromArray($rawEvent),
            $rawEvents
        );

        return new self($events);
    }

    private function validate(): void
    {
        if (empty($this->events)) {
            throw new \InvalidArgumentException('Events array cannot be empty');
        }

        foreach ($this->events as $event) {
            if (!$event instanceof Event) {
                throw new \InvalidArgumentException('All events must be instances of Event');
            }
        }
    }
}
