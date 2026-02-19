<?php

declare(strict_types=1);

namespace Test\Unit\Shared\Infrastructure\Symfony\Bus\Serializer;

use Ingestor\Application\IngestEventMessage;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shared\Infrastructure\Symfony\Bus\Serializer\SymfonyMessageSerializer;
use Symfony\Component\Messenger\Envelope;
use Test\ObjectMother\Ingestor\Application\DTO\EventDTOMother;

final class SymfonyMessageSerializerTest extends TestCase
{
    private SymfonyMessageSerializer $serializer;

    public function setUp(): void
    {
        $this->serializer = new SymfonyMessageSerializer();
    }

    #[Test]
    public function encodesMessageToJsonStructure(): void
    {
        $message  = new IngestEventMessage(EventDTOMother::create());
        $envelope = new Envelope($message);

        $encoded = $this->serializer->encode($envelope);

        $body = json_decode($encoded['body'], true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('payload', $body);
        self::assertArrayHasKey('metadata', $body);
        self::assertArrayHasKey('name', $body['metadata']);
        self::assertSame('ingestor.application.ingest_event_message', $body['metadata']['name']);
    }

    #[Test]
    public function decodesEncodedMessageBackToOriginal(): void
    {
        $message  = new IngestEventMessage(EventDTOMother::create());
        $envelope = new Envelope($message);

        $encoded        = $this->serializer->encode($envelope);
        $decoded        = $this->serializer->decode($encoded);

        self::assertInstanceOf(IngestEventMessage::class, $decoded->getMessage());

        /** @var IngestEventMessage $decodedMessage */
        $decodedMessage = $decoded->getMessage();
        self::assertSame('42', $decodedMessage->eventDto->eventId);
        self::assertSame('100', $decodedMessage->eventDto->baseEvent->baseEventId);
        self::assertSame('online', $decodedMessage->eventDto->baseEvent->sellMode);
    }

    #[Test]
    public function encodeAndDecodeAreSymmetric(): void
    {
        $original = new IngestEventMessage(EventDTOMother::create());
        $envelope = new Envelope($original);

        $encoded = $this->serializer->encode($envelope);
        $decoded = $this->serializer->decode($encoded);

        /** @var IngestEventMessage $decodedMessage */
        $decodedMessage = $decoded->getMessage();

        self::assertSame(
            $original->toPrimitives(),
            $decodedMessage->toPrimitives(),
        );
    }
}
