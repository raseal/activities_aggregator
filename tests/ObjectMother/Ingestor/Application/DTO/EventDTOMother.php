<?php

declare(strict_types=1);

namespace Test\ObjectMother\Ingestor\Application\DTO;

use Ingestor\Application\DTO\BaseEvent;
use Ingestor\Application\DTO\Event as EventDTO;

final class EventDTOMother
{
    public static function create(
        string $eventId = '42',
        string $baseEventId = '100',
        string $sellMode = 'online',
        string $title = 'Test Event',
        ?string $organizerCompanyId = null,
        string $eventStartDate = '2025-01-01 10:00:00',
        string $eventEndDate = '2025-01-01 12:00:00',
        string $sellFrom = '2024-06-01 00:00:00',
        string $sellTo = '2025-01-01 09:00:00',
        bool $soldOut = false,
        array $zones = [],
    ): EventDTO {
        return new EventDTO(
            baseEvent: new BaseEvent(
                baseEventId: $baseEventId,
                sellMode: $sellMode,
                title: $title,
                organizerCompanyId: $organizerCompanyId,
            ),
            eventId: $eventId,
            eventStartDate: new \DateTimeImmutable($eventStartDate),
            eventEndDate: new \DateTimeImmutable($eventEndDate),
            sellFrom: new \DateTimeImmutable($sellFrom),
            sellTo: new \DateTimeImmutable($sellTo),
            soldOut: $soldOut,
            zones: $zones,
        );
    }
}

