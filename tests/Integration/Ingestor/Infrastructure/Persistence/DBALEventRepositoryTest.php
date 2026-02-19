<?php

declare(strict_types=1);

namespace Test\Integration\Ingestor\Infrastructure\Persistence;

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
use Ingestor\Infrastructure\Persistence\DBALEventRepository;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\NullLogger;
use Test\DBALTestCase;

final class DBALEventRepositoryTest extends DBALTestCase
{
    private DBALEventRepository $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = new DBALEventRepository($this->connection(), new NullLogger());
    }

    #[Test]
    public function savesEventWithZones(): void
    {
        $event = $this->buildEvent(eventId: 1, baseEventId: 10, zones: [
            $this->buildZone(zoneId: 1, name: 'Pista', capacity: 100, price: 1500),
            $this->buildZone(zoneId: 2, name: 'VIP',   capacity: 50,  price: 5000),
        ]);

        $this->repository->save($event);

        $row = $this->fetchEvent(eventId: 1, baseEventId: 10);
        self::assertSame('Rock Concert 2025', $row['title']);
        self::assertSame('online', $row['sell_mode']);
        self::assertSame(0, (int) $row['sold_out']);

        $zones = $this->fetchZones(eventId: 1, baseEventId: 10);
        self::assertCount(2, $zones);
        self::assertSame('Pista', $zones[0]['zone_name']);
        self::assertSame('VIP',   $zones[1]['zone_name']);
    }

    #[Test]
    public function savesEventWithoutZones(): void
    {
        $event = $this->buildEvent(eventId: 2, baseEventId: 20, zones: []);

        $this->repository->save($event);

        $row = $this->fetchEvent(eventId: 2, baseEventId: 20);
        self::assertNotNull($row);

        $zones = $this->fetchZones(eventId: 2, baseEventId: 20);
        self::assertCount(0, $zones);
    }

    #[Test]
    public function upsertUpdatesExistingEvent(): void
    {
        $event = $this->buildEvent(eventId: 3, baseEventId: 30, title: 'Original Title', zones: []);
        $this->repository->save($event);

        $updated = $this->buildEvent(eventId: 3, baseEventId: 30, title: 'Updated Title', zones: []);
        $this->repository->save($updated);

        $row = $this->fetchEvent(eventId: 3, baseEventId: 30);
        self::assertSame('Updated Title', $row['title']);
    }

    #[Test]
    public function upsertReplacesZonesOnUpdate(): void
    {
        $event = $this->buildEvent(eventId: 4, baseEventId: 40, zones: [
            $this->buildZone(zoneId: 1, name: 'Pista', capacity: 100, price: 1500),
            $this->buildZone(zoneId: 2, name: 'VIP',   capacity: 50,  price: 5000),
        ]);
        $this->repository->save($event);

        $updated = $this->buildEvent(eventId: 4, baseEventId: 40, zones: [
            $this->buildZone(zoneId: 1, name: 'Pista Actualizada', capacity: 200, price: 2000),
        ]);
        $this->repository->save($updated);

        $zones = $this->fetchZones(eventId: 4, baseEventId: 40);
        self::assertCount(1, $zones);
        self::assertSame('Pista Actualizada', $zones[0]['zone_name']);
        self::assertSame(200, (int) $zones[0]['capacity']);
    }

    #[Test]
    public function savesEventWithNullableOrganizerCompanyId(): void
    {
        $event = $this->buildEvent(eventId: 5, baseEventId: 50, organizerCompanyId: null, zones: []);

        $this->repository->save($event);

        $row = $this->fetchEvent(eventId: 5, baseEventId: 50);
        self::assertNull($row['organizer_company_id']);
    }

    // --- Helpers ---

    private function buildEvent(
        int $eventId,
        int $baseEventId,
        string $title = 'Rock Concert 2025',
        ?int $organizerCompanyId = 99,
        array $zones = [],
    ): Event {
        $zonesCollection = new Zones([]);
        foreach ($zones as $zone) {
            $zonesCollection->add($zone);
        }

        return Event::create(
            new EventId($eventId),
            new BaseEventId($baseEventId),
            SellMode::ONLINE,
            new EventTitle($title),
            $organizerCompanyId !== null ? new OrganizerCompanyId($organizerCompanyId) : null,
            EventDatePeriod::fromDates(
                new \DateTimeImmutable('2025-06-01 10:00:00'),
                new \DateTimeImmutable('2025-06-01 12:00:00'),
            ),
            SellPeriod::fromDates(
                new \DateTimeImmutable('2025-01-01 00:00:00'),
                new \DateTimeImmutable('2025-05-31 23:59:59'),
            ),
            false,
            $zonesCollection,
        );
    }

    private function buildZone(int $zoneId, string $name, int $capacity, int $price): Zone
    {
        return new Zone(
            new ZoneId($zoneId),
            new Capacity($capacity),
            new Price($price),
            new ZoneName($name),
            false,
        );
    }

    private function fetchEvent(int $eventId, int $baseEventId): ?array
    {
        $row = $this->connection()->fetchAssociative(
            'SELECT * FROM events WHERE event_id = ? AND base_event_id = ?',
            [$eventId, $baseEventId],
        );

        return $row ?: null;
    }

    private function fetchZones(int $eventId, int $baseEventId): array
    {
        return $this->connection()->fetchAllAssociative(
            'SELECT * FROM event_zones WHERE event_id = ? AND base_event_id = ? ORDER BY zone_id',
            [$eventId, $baseEventId],
        );
    }
}


