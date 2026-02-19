<?php

declare(strict_types=1);

namespace Test\Integration\Ingestor\Infrastructure;

use Ingestor\Infrastructure\XmlEventsStreamReader;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class XmlEventsStreamReaderTest extends TestCase
{
    private XmlEventsStreamReader $reader;

    public function setUp(): void
    {
        $this->reader = new XmlEventsStreamReader();
    }

    #[Test]
    public function parsesAllEventsAndZonesFromXml(): void
    {
        $xml = $this->xmlWithEvents(3);

        $events = $this->collectAllEvents($xml);

        self::assertCount(3, $events);

        self::assertSame('291', $events[0]['event_id']);
        self::assertSame('100', $events[0]['base_event']['base_event_id']);
        self::assertSame('online', $events[0]['base_event']['sell_mode']);
        self::assertSame('Concert A', $events[0]['base_event']['title']);
        self::assertFalse($events[0]['sold_out']);
        self::assertCount(2, $events[0]['zones']);
        self::assertSame('Pista', $events[0]['zones'][0]['name']);
        self::assertSame(20.0, $events[0]['zones'][0]['price']);
        self::assertTrue($events[0]['zones'][0]['numbered']);
    }

    #[Test]
    public function returnsEmptyWhenXmlHasNoEvents(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<eventList version="1.0">
    <output></output>
</eventList>
XML;

        $events = $this->collectAllEvents($xml);

        self::assertCount(0, $events);
    }

    #[Test]
    public function yieldsBatchesOfConfiguredSize(): void
    {
        $xml = $this->xmlWithEvents(55);

        $batches = iterator_to_array($this->reader->readInBatches($xml), false);

        self::assertCount(2, $batches);
        self::assertCount(50, $batches[0]);
        self::assertCount(5, $batches[1]);
    }

    #[Test]
    public function yieldsOnlyOneBatchWhenEventsAreLessThanBatchSize(): void
    {
        $xml = $this->xmlWithEvents(3);

        $batches = iterator_to_array($this->reader->readInBatches($xml), false);

        self::assertCount(1, $batches);
        self::assertCount(3, $batches[0]);
    }

    #[Test]
    public function readReturnsCorrectStats(): void
    {
        $xml = $this->xmlWithEvents(3);

        $collectedEvents = [];
        $stats = $this->reader->read($xml, function (array $batch) use (&$collectedEvents): void {
            $collectedEvents = array_merge($collectedEvents, $batch);
        });

        self::assertSame(3, $stats['total_events']);
        self::assertSame(1, $stats['total_batches']);
        self::assertCount(3, $collectedEvents);
    }

    #[Test]
    public function parsesEventWithNullableOrganizerCompanyId(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<eventList version="1.0">
    <output>
        <base_event base_event_id="100" sell_mode="online" title="No Organizer">
            <event event_id="1" event_start_date="2025-01-01T10:00:00" event_end_date="2025-01-01T12:00:00"
                   sell_from="2024-01-01T00:00:00" sell_to="2025-01-01T09:00:00" sold_out="false">
            </event>
        </base_event>
    </output>
</eventList>
XML;

        $events = $this->collectAllEvents($xml);

        self::assertSame('', $events[0]['base_event']['organizer_company_id']);
    }

    #[Test]
    public function parsesSoldOutFlagCorrectly(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<eventList version="1.0">
    <output>
        <base_event base_event_id="100" sell_mode="online" title="Sold Out Event">
            <event event_id="1" event_start_date="2025-01-01T10:00:00" event_end_date="2025-01-01T12:00:00"
                   sell_from="2024-01-01T00:00:00" sell_to="2025-01-01T09:00:00" sold_out="true">
            </event>
        </base_event>
    </output>
</eventList>
XML;

        $events = $this->collectAllEvents($xml);

        self::assertTrue($events[0]['sold_out']);
    }

    // --- Helpers ---

    private function collectAllEvents(string $xml): array
    {
        $all = [];
        $this->reader->read($xml, function (array $batch) use (&$all): void {
            $all = array_merge($all, $batch);
        });

        return $all;
    }

    private function xmlWithEvents(int $count): string
    {
        $events = '';
        for ($i = 1; $i <= $count; $i++) {
            $events .= <<<XML

        <base_event base_event_id="100" sell_mode="online" title="Concert A">
            <event event_id="291" event_start_date="2025-01-01T10:00:00" event_end_date="2025-01-01T12:00:00"
                   sell_from="2024-01-01T00:00:00" sell_to="2025-01-01T09:00:00" sold_out="false">
                <zone zone_id="1" capacity="100" price="20.00" name="Pista" numbered="true" />
                <zone zone_id="2" capacity="50"  price="15.00" name="VIP"   numbered="false" />
            </event>
        </base_event>
XML;
        }

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<eventList version="1.0">
    <output>
        $events
    </output>
</eventList>
XML;
    }
}

