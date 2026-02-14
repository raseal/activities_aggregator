<?php

declare(strict_types=1);

namespace Ingestor\Application;

use Ingestor\Application\DTO\Event as EventDTO;
use Shared\Application\Bus\Message\Message;

final readonly class IngestEventMessage implements Message
{
    public function __construct(
        public EventDTO $eventDto
    ) {}

    public function toPrimitives(): array
    {
        return [
            'eventId' => $this->eventDto->eventId,
            'baseEvent' => [
                'baseEventId' => $this->eventDto->baseEvent->baseEventId,
                'sellMode' => $this->eventDto->baseEvent->sellMode,
                'title' => $this->eventDto->baseEvent->title,
                'organizerCompanyId' => $this->eventDto->baseEvent->organizerCompanyId,
            ],
            'eventStartDate' => $this->eventDto->eventStartDate->format('c'),
            'eventEndDate' => $this->eventDto->eventEndDate->format('c'),
            'sellFrom' => $this->eventDto->sellFrom->format('c'),
            'sellTo' => $this->eventDto->sellTo->format('c'),
            'soldOut' => $this->eventDto->soldOut,
            'zones' => array_map(
                static fn($zone) => [
                    'zoneId' => $zone->zoneId,
                    'capacity' => $zone->capacity,
                    'price' => $zone->price,
                    'name' => $zone->name,
                    'numbered' => $zone->numbered,
                ],
                $this->eventDto->zones
            ),
        ];
    }

    public static function fromPrimitives(array $data): self
    {
        return new self(
            EventDTO::fromArray([
                'event_id' => $data['eventId'],
                'base_event' => [
                    'base_event_id' => $data['baseEvent']['baseEventId'],
                    'sell_mode' => $data['baseEvent']['sellMode'],
                    'title' => $data['baseEvent']['title'],
                    'organizer_company_id' => $data['baseEvent']['organizerCompanyId'],
                ],
                'event_start_date' => $data['eventStartDate'],
                'event_end_date' => $data['eventEndDate'],
                'sell_from' => $data['sellFrom'],
                'sell_to' => $data['sellTo'],
                'sold_out' => $data['soldOut'],
                'zones' => array_map(
                    static fn($zone) => [
                        'zone_id' => $zone['zoneId'],
                        'capacity' => $zone['capacity'],
                        'price' => $zone['price'],
                        'name' => $zone['name'],
                        'numbered' => $zone['numbered'],
                    ],
                    $data['zones']
                ),
            ])
        );
    }
}
