<?php

declare(strict_types=1);

namespace Ingestor\Application;

use Ingestor\Application\DTO\Event as EventDTO;
use Ingestor\Domain\Event;
use Ingestor\Domain\ValueObject\BaseEventId;
use Ingestor\Domain\ValueObject\EventDatePeriod;
use Ingestor\Domain\ValueObject\EventId;
use Ingestor\Domain\ValueObject\EventTitle;
use Ingestor\Domain\ValueObject\OrganizerCompanyId;
use Ingestor\Domain\ValueObject\SellMode;
use Ingestor\Domain\ValueObject\SellPeriod;
use Ingestor\Domain\ValueObject\Zone\Capacity;
use Ingestor\Domain\ValueObject\Zone\Price;
use Ingestor\Domain\ValueObject\Zone\Zone;
use Ingestor\Domain\ValueObject\Zone\ZoneId;
use Ingestor\Domain\ValueObject\Zone\ZoneName;
use Ingestor\Domain\ValueObject\Zone\Zones;

final readonly class EventFactory
{
    public function fromDTO(EventDTO $eventDto): Event
    {
        return Event::create(
            new EventId((int) $eventDto->eventId),
            new BaseEventId((int) $eventDto->baseEvent->baseEventId),
            SellMode::fromString($eventDto->baseEvent->sellMode),
            new EventTitle($eventDto->baseEvent->title),
            $this->createOrganizerCompanyId($eventDto->baseEvent->organizerCompanyId),
            EventDatePeriod::fromDates(
                $eventDto->eventStartDate,
                $eventDto->eventEndDate
            ),
            SellPeriod::fromDates(
                $eventDto->sellFrom,
                $eventDto->sellTo
            ),
            $eventDto->soldOut,
            $this->createZones($eventDto->zones),
        );
    }

    private function createOrganizerCompanyId(?string $organizerCompanyId): ?OrganizerCompanyId
    {
        return $organizerCompanyId !== null
            ? new OrganizerCompanyId((int) $organizerCompanyId)
            : null;
    }

    /**
     * @param DTO\Zone[] $zoneDtos
     */
    private function createZones(array $zoneDtos): Zones
    {
        $zones = new Zones([]);

        foreach ($zoneDtos as $zoneDto) {
            $priceInCents = (int)($zoneDto->price * 100);
            $zones->add(
                new Zone(
                    new ZoneId((int) $zoneDto->zoneId),
                    new Capacity($zoneDto->capacity),
                    new Price($priceInCents),
                    new ZoneName($zoneDto->name),
                    $zoneDto->numbered,
                )
            );
        }

        return $zones;
    }
}
