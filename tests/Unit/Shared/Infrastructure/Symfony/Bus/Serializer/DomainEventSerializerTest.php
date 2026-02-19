<?php

declare(strict_types=1);

namespace Test\Unit\Shared\Infrastructure\Symfony\Bus\Serializer;

use Ingestor\Domain\Event\EventCreated;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shared\Infrastructure\Symfony\Bus\Serializer\DomainEventSerializer;
use Symfony\Component\Messenger\Envelope;

final class DomainEventSerializerTest extends TestCase
{
    private DomainEventSerializer $serializer;

    public function setUp(): void
    {
        $this->serializer = new DomainEventSerializer();
    }

    #[Test]
    public function encodesDomainEventToJsonStructure(): void
    {
        $event = $this->buildEventCreated();
        $envelope = new Envelope($event);

        $encoded = $this->serializer->encode($envelope);

        $body = json_decode($encoded['body'], true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('data', $body);
        self::assertSame('ingestor.event.created', $body['data']['type']);
        self::assertArrayHasKey('attributes', $body['data']);
        self::assertSame(EventCreated::class, $encoded['headers']['type']);
    }

    #[Test]
    public function decodesEncodedEventBackToOriginal(): void
    {
        $event    = $this->buildEventCreated();
        $envelope = new Envelope($event);

        $encoded = $this->serializer->encode($envelope);
        $decoded = $this->serializer->decode($encoded);

        self::assertInstanceOf(EventCreated::class, $decoded->getMessage());

        /** @var EventCreated $decodedEvent */
        $decodedEvent = $decoded->getMessage();
        self::assertSame($event->aggregateId(), $decodedEvent->aggregateId());
        self::assertSame($event->toPrimitives(), $decodedEvent->toPrimitives());
    }

    #[Test]
    public function encodeAndDecodeAreSymmetric(): void
    {
        $event    = $this->buildEventCreated();
        $envelope = new Envelope($event);

        $encoded = $this->serializer->encode($envelope);
        $decoded = $this->serializer->decode($encoded);

        /** @var EventCreated $decodedEvent */
        $decodedEvent = $decoded->getMessage();

        self::assertSame($event->eventId(), $decodedEvent->eventId());
        self::assertSame($event->occurredOn(), $decodedEvent->occurredOn());
        self::assertSame($event->toPrimitives(), $decodedEvent->toPrimitives());
    }

    #[Test]
    public function throwsWhenTypeHeaderIsMissing(): void
    {
        $encoded = [
            'body' => json_encode(['data' => ['aggregate_id' => '1', 'attributes' => [], 'id' => 'uuid', 'occurred_on' => 'now']], JSON_THROW_ON_ERROR),
            'headers' => [],
        ];

        $this->expectException(\RuntimeException::class);

        $this->serializer->decode($encoded);
    }

    // --- Helpers ---

    private function buildEventCreated(): EventCreated
    {
        return EventCreated::fromPrimitives(
            aggregateId: '29100',
            body: [
                'id'                   => 291,
                'base_event_id'        => 100,
                'sell_mode'            => 'online',
                'title'                => 'Test Concert',
                'organizer_company_id' => null,
                'event_start_date'     => '2025-01-01T10:00:00+00:00',
                'event_end_date'       => '2025-01-01T12:00:00+00:00',
                'sell_from'            => '2024-06-01T00:00:00+00:00',
                'sell_to'              => '2025-01-01T09:00:00+00:00',
                'sold_out'             => false,
                'zones'                => [],
            ],
            eventId: 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
            occurredOn: '2025-01-01T00:00:00+00:00',
        );
    }
}

