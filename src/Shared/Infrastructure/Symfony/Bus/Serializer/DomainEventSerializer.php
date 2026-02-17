<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Symfony\Bus\Serializer;

use Shared\Domain\Event\DomainEvent;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

final class DomainEventSerializer implements SerializerInterface
{
    public function decode(array $encodedEnvelope): Envelope
    {
        $body = $encodedEnvelope['body'];
        if (is_array($body)) {
            $body = json_encode($body, JSON_THROW_ON_ERROR);
        }

        $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        // Get the event class from headers
        $eventClass = $encodedEnvelope['headers']['type'] ?? null;
        if (!$eventClass || !class_exists($eventClass)) {
            throw new \RuntimeException('Cannot decode domain event: missing or invalid type header');
        }

        // Reconstruct the DomainEvent using fromPrimitives
        /** @var DomainEvent $event */
        $event = $eventClass::fromPrimitives(
            aggregateId: $data['data']['aggregate_id'],
            body: $data['data']['attributes'],
            eventId: $data['data']['id'],
            occurredOn: $data['data']['occurred_on']
        );

        return new Envelope($event);
    }

    public function encode(Envelope $envelope): array
    {
        /** @var DomainEvent $event */
        $event = $envelope->getMessage();

        if (!$event instanceof DomainEvent) {
            throw new \InvalidArgumentException(
                sprintf('Expected DomainEvent, got %s', get_class($event))
            );
        }

        // Create a standardized JSON structure for domain events
        $payload = [
            'data' => [
                'id' => $event->eventId(),
                'type' => $event::eventName(),
                'occurred_on' => $event->occurredOn(),
                'aggregate_id' => $event->aggregateId(),
                'attributes' => $event->toPrimitives(),
            ],
        ];

        return [
            'body' => json_encode($payload, JSON_THROW_ON_ERROR),
            'headers' => [
                'type' => $event::class,
                'event_name' => $event::eventName(),
                'Content-Type' => 'application/json',
            ],
        ];
    }
}

