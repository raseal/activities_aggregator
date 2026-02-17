<?php

declare(strict_types=1);

namespace Ingestor\Domain\Event;

use Ingestor\Domain\Event as EventAR;
use Ingestor\Domain\ValueObject\Zone\Zone;
use Ingestor\Domain\ValueObject\Zone\Zones;
use Shared\Domain\Event\DomainEvent;

final class EventCreated extends DomainEvent
{
    public function __construct(
        string $aggregateId,
        private readonly int $baseEventId,
        private readonly string $sellMode,
        private readonly string $title,
        private readonly ?int $organizerCompanyId,
        private readonly string $eventStartDate,
        private readonly string $eventEndDate,
        private readonly string $sellFrom,
        private readonly string $sellTo,
        private readonly bool $soldOut,
        private readonly array $zones,
        ?string $eventId = null,
        ?string $occurredOn = null,
    ) {
        parent::__construct($aggregateId, $eventId, $occurredOn);
    }

    public static function fromAggregateRoot(EventAR $event): self
    {
        return new self(
            aggregateId: sprintf('%d-%d', $event->id()->value(), $event->baseEventId()->value()),
            baseEventId: $event->baseEventId()->value(),
            sellMode: $event->sellMode()->value,
            title: $event->title()->value(),
            organizerCompanyId: $event->organizerCompanyId()?->value(),
            eventStartDate: $event->eventPeriod()->startDate()->format(\DateTimeInterface::ATOM),
            eventEndDate: $event->eventPeriod()->endDate()->format(\DateTimeInterface::ATOM),
            sellFrom: $event->sellPeriod()->sellFrom()->format(\DateTimeInterface::ATOM),
            sellTo: $event->sellPeriod()->sellTo()->format(\DateTimeInterface::ATOM),
            soldOut: $event->isSoldOut(),
            zones: self::extractZones($event->zones()),
        );
    }

    private static function extractZones(Zones $zones): array
    {
        $primitives = [];

        /** @var Zone $zone */
        foreach ($zones as $zone) {
            $primitives[] = [
                'zone_id' => $zone->id->value(),
                'capacity' => $zone->capacity->value(),
                'price' => $zone->price->value(),
                'name' => $zone->name->value(),
                'numbered' => $zone->isNumbered,
            ];
        }

        return $primitives;
    }

    public static function eventName(): string
    {
        return 'ingestor.event.created';
    }

    public static function fromPrimitives(
        string $aggregateId,
        array $body,
        string $eventId,
        string $occurredOn,
    ): self {
        return new self(
            aggregateId: $aggregateId,
            baseEventId: $body['base_event_id'],
            sellMode: $body['sell_mode'],
            title: $body['title'],
            organizerCompanyId: $body['organizer_company_id'] ?? null,
            eventStartDate: $body['event_start_date'],
            eventEndDate: $body['event_end_date'],
            sellFrom: $body['sell_from'],
            sellTo: $body['sell_to'],
            soldOut: $body['sold_out'],
            zones: $body['zones'],
            eventId: $eventId,
            occurredOn: $occurredOn,
        );
    }

    public function toPrimitives(): array
    {
        return [
            'base_event_id' => $this->baseEventId,
            'sell_mode' => $this->sellMode,
            'title' => $this->title,
            'organizer_company_id' => $this->organizerCompanyId,
            'event_start_date' => $this->eventStartDate,
            'event_end_date' => $this->eventEndDate,
            'sell_from' => $this->sellFrom,
            'sell_to' => $this->sellTo,
            'sold_out' => $this->soldOut,
            'zones' => $this->zones,
        ];
    }

    public function baseEventId(): int
    {
        return $this->baseEventId;
    }

    public function sellMode(): string
    {
        return $this->sellMode;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function organizerCompanyId(): ?int
    {
        return $this->organizerCompanyId;
    }

    public function eventStartDate(): string
    {
        return $this->eventStartDate;
    }

    public function eventEndDate(): string
    {
        return $this->eventEndDate;
    }

    public function sellFrom(): string
    {
        return $this->sellFrom;
    }

    public function sellTo(): string
    {
        return $this->sellTo;
    }

    public function soldOut(): bool
    {
        return $this->soldOut;
    }

    public function zones(): array
    {
        return $this->zones;
    }
}
