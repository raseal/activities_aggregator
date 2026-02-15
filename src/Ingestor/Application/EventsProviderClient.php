<?php

declare(strict_types=1);

namespace Ingestor\Application;

use Ingestor\Application\DTO\Event;

interface EventsProviderClient
{
    /**
     * Fetch events from external provider.
     *
     * @return iterable<Event> Stream of events (memory efficient for large datasets)
     */
    public function fetchEvents(): iterable;
}

