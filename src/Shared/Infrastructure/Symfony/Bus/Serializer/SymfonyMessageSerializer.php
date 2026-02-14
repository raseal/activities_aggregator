<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Symfony\Bus\Serializer;

use Shared\Domain\Event\DomainEvent;
use Shared\Infrastructure\Symfony\Bus\SymfonyIdStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

final class SymfonyMessageSerializer implements SerializerInterface
{
    private const string CLASS_SEPARATOR = '.';

    public function decode(array $encodedEnvelope): Envelope
    {
        $data = json_decode($encodedEnvelope['body'], true, 512, JSON_THROW_ON_ERROR);
        $messageClass = $this->decodeMessageName($data['metadata']['name']);
        $message = $messageClass::fromPrimitives($data['payload']);

        /** @var array $stampsNested */
        $stampsNested = unserialize($encodedEnvelope['headers']['stamps'], ['allowed_classes' => true]);
        $stamps = array_merge(...$stampsNested);

        return new Envelope($message, $stamps);
    }

    public function encode(Envelope $envelope): array
    {
        /** @var DomainEvent $message */
        $message = $envelope->getMessage();
        $allStamps = array_values($envelope->all());
        $id = $envelope->last(SymfonyIdStamp::class);

        $data = [
            'payload' => $message->toPrimitives(),
            'metadata' => [
                'id' => $id?->value(),
                'name' => $this->encodeMessageName($message::class),
            ],
        ];

        return [
            'body' => json_encode($data, JSON_THROW_ON_ERROR),
            'headers' => [
                'stamps' => serialize($allStamps),
            ],
        ];
    }

    private function decodeMessageName(string $message): string
    {
        $text = array_map(static function (string $part) {
            return ucwords($part, '_');
        }, explode(self::CLASS_SEPARATOR, $message));

        return str_replace('_', '', implode('\\', $text));
    }

    private function encodeMessageName(string $message): string
    {
        return strtolower(
            preg_replace(
                '/([a-z])([A-Z])/',
                '$1_$2',
                str_replace('\\', self::CLASS_SEPARATOR, $message)
            )
        );
    }
}
