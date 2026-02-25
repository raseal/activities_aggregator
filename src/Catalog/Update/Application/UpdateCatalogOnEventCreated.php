<?php

declare(strict_types=1);

namespace Catalog\Update\Application;

use Catalog\Search\CatalogItemView;
use Catalog\Search\CatalogZoneView;
use Ingestor\Domain\Event\EventCreated;
use Shared\Application\Bus\Event\EventSubscriber;

final readonly class UpdateCatalogOnEventCreated implements EventSubscriber
{
    public function __construct(
        private UpdateCatalog $updateCatalog,
    ) {
    }

    public static function subscribedTo(): array
    {
        return [
            EventCreated::class,
        ];
    }

    public function __invoke(EventCreated $event): void
    {
        $itemCatalogView = $this->parseEvent($event);
        $this->updateCatalog->__invoke($itemCatalogView);
    }

    private function parseEvent(EventCreated $event): CatalogItemView
    {
        return new CatalogItemView(
            id: $event->aggregateId(),
            eventId: $event->id(),
            baseEventId: $event->baseEventId(),
            title: $event->title(),
            sellMode: $event->sellMode(),
            organizerCompanyId: $event->organizerCompanyId(),
            eventStartDate: new \DateTimeImmutable($event->eventStartDate()),
            eventEndDate: new \DateTimeImmutable($event->eventEndDate()),
            sellFrom: new \DateTimeImmutable($event->sellFrom()),
            sellTo: new \DateTimeImmutable($event->sellTo()),
            soldOut: $event->soldOut(),
            zones: $this->parseZones($event->zones()),

        );
    }

    private function parseZones(array $zones): array
    {
        $parsedZones = [];

        foreach ($zones as $zone) {
            $parsedZones[] = new CatalogZoneView(
                $zone['zone_id'],
                $zone['capacity'],
                $zone['price'],
                $zone['name'],
                $zone['numbered'],
            );
        }

        return $parsedZones;
    }
}
