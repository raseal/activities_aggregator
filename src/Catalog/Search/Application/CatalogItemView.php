<?php

declare(strict_types=1);

namespace Catalog\Search\Application;

final readonly class CatalogItemView
{
    public function __construct(
        public string $id,
        public int $eventId,
        public int $baseEventId,
        public string $title,
        public string $sellMode,
        public ?int $organizerCompanyId,
        public \DateTimeImmutable $eventStartDate,
        public \DateTimeImmutable $eventEndDate,
        public \DateTimeImmutable $sellFrom,
        public \DateTimeImmutable $sellTo,
        public bool $soldOut,
        /** @var CatalogZoneView[] */
        public array $zones,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'event_id' => $this->eventId,
            'base_event_id' => $this->baseEventId,
            'title' => $this->title,
            'sell_mode' => $this->sellMode,
            'organizer_company_id' => $this->organizerCompanyId,
            'event_start_date' => $this->eventStartDate->format(\DateTimeInterface::ATOM),
            'event_end_date' => $this->eventEndDate->format(\DateTimeInterface::ATOM),
            'sell_from' => $this->sellFrom->format(\DateTimeInterface::ATOM),
            'sell_to' => $this->sellTo->format(\DateTimeInterface::ATOM),
            'sold_out' => $this->soldOut,
            'zones' => array_map(static fn ($z) => $z->toArray(), $this->zones),
        ];
    }
}
