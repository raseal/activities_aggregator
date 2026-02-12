<?php

declare(strict_types=1);

namespace Ingestor\Infrastructure;

use XMLReader;

final class XmlEventsStreamReader
{
    private const int BATCH_SIZE = 50;

    public function readInBatches(string $xmlContent): \Generator
    {
        $reader = new XMLReader();
        $reader->XML($xmlContent);

        $currentBatch = [];
        $batchCount = 0;

        while ($reader->read()) {
            if ($reader->nodeType === XMLReader::ELEMENT && $reader->name === 'base_event') {
                $baseEventXml = simplexml_load_string($reader->readOuterXml());

                if ($baseEventXml === false) {
                    continue; // Skip invalid XML nodes
                }

                $baseEventData = $this->extractBaseEventData($baseEventXml);

                foreach ($baseEventXml->event as $event) {
                    $eventData = $this->extractEventData($event, $baseEventData);

                    $currentBatch[] = $eventData;
                    $batchCount++;

                    if ($batchCount >= self::BATCH_SIZE) {
                        yield $currentBatch;
                        $currentBatch = [];
                        $batchCount = 0;
                    }
                }
            }
        }

        // Yield remaining events in the last batch
        if (!empty($currentBatch)) {
            yield $currentBatch;
        }

        $reader->close();
    }

    /**
     * @param string $xmlContent The XML content to process
     * @param callable $onBatch Callback function to process each batch: function(array $events): void
     * @return array{total_events: int, total_batches: int}
     */
    public function read(string $xmlContent, callable $onBatch): array
    {
        $totalEvents = 0;
        $totalBatches = 0;

        foreach ($this->readInBatches($xmlContent) as $batch) {
            $onBatch($batch);
            $totalEvents += count($batch);
            $totalBatches++;
        }

        return [
            'total_events' => $totalEvents,
            'total_batches' => $totalBatches,
        ];
    }

    private function extractBaseEventData(\SimpleXMLElement $baseEventXml): array
    {
        return [
            'base_event_id' => (string) $baseEventXml['base_event_id'],
            'sell_mode' => (string) $baseEventXml['sell_mode'],
            'title' => (string) $baseEventXml['title'],
            'organizer_company_id' => (string) ($baseEventXml['organizer_company_id'] ?? ''),
        ];
    }

    private function extractEventData(\SimpleXMLElement $event, array $baseEventData): array
    {
        $zones = [];
        foreach ($event->zone as $zone) {
            $zones[] = [
                'zone_id' => (string) $zone['zone_id'],
                'capacity' => (int) $zone['capacity'],
                'price' => (float) $zone['price'],
                'name' => (string) $zone['name'],
                'numbered' => (string) $zone['numbered'] === 'true',
            ];
        }

        return [
            'base_event' => $baseEventData,
            'event_id' => (string) $event['event_id'],
            'event_start_date' => (string) $event['event_start_date'],
            'event_end_date' => (string) $event['event_end_date'],
            'sell_from' => (string) $event['sell_from'],
            'sell_to' => (string) $event['sell_to'],
            'sold_out' => (string) $event['sold_out'] === 'true',
            'zones' => $zones,
        ];
    }
}
