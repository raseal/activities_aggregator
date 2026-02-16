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
        $body = $encodedEnvelope['body'];
        if (is_array($body)) {
            $body = json_encode($body, JSON_THROW_ON_ERROR);
        }

        $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        $messageClass = $this->decodeMessageName($data['metadata']['name']);
        $message = $messageClass::fromPrimitives($data['payload']);

        $stamps = [];
        if (isset($encodedEnvelope['headers']['stamps'])) {
            $stampsJson = $encodedEnvelope['headers']['stamps'];
            $stampsData = json_decode($stampsJson, true, 512, JSON_THROW_ON_ERROR);
            $stamps = $this->deserializeStamps($stampsData);
        }

        return new Envelope($message, $stamps);
    }

    public function encode(Envelope $envelope): array
    {
        /** @var DomainEvent $message */
        $message = $envelope->getMessage();
        $allStamps = array_values($envelope->all());
        $id = $envelope->last(SymfonyIdStamp::class);

        $payload = [
            'payload' => $message->toPrimitives(),
            'metadata' => [
                'id' => $id?->value(),
                'name' => $this->encodeMessageName($message::class),
            ],
        ];

        return [
            'body' => json_encode($payload, JSON_THROW_ON_ERROR),
            'headers' => [
                'stamps' => json_encode($this->serializeStamps($allStamps), JSON_THROW_ON_ERROR),
                'type' => $message::class,
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

    private function serializeStamps(array $stamps): array
    {
        $serialized = [];

        foreach ($stamps as $stampGroup) {
            foreach ($stampGroup as $stamp) {
                $serialized[] = [
                    'class' => get_class($stamp),
                    'data' => $this->serializeStamp($stamp),
                ];
            }
        }

        return $serialized;
    }

    private function serializeStamp(object $stamp): array
    {
        $reflection = new \ReflectionClass($stamp);
        $data = [];

        foreach ($reflection->getProperties() as $property) {
            $value = $property->getValue($stamp);

            if (is_object($value)) {
                $data[$property->getName()] = [
                    '__class' => get_class($value),
                    '__value' => method_exists($value, '__toString') ? (string)$value : null,
                ];
            } else {
                $data[$property->getName()] = $value;
            }
        }

        return $data;
    }

    private function deserializeStamps(array $stampsData): array
    {
        $stamps = [];

        foreach ($stampsData as $stampInfo) {
            $class = $stampInfo['class'];

            if (!class_exists($class)) {
                continue; // Skip unknown stamps
            }

            try {
                $stamps[] = $this->deserializeStamp($class, $stampInfo['data']);
            } catch (\Throwable) {
                // Skip stamps that can't be deserialized
                continue;
            }
        }

        return $stamps;
    }

    private function deserializeStamp(string $class, array $data): object
    {
        $reflection = new \ReflectionClass($class);
        $instance = $reflection->newInstanceWithoutConstructor();

        foreach ($data as $propertyName => $value) {
            try {
                $property = $reflection->getProperty($propertyName);

                // Skip deserializing complex values
                if (is_array($value) && isset($value['__class'])) {
                    continue;
                }

                $property->setValue($instance, $value);
            } catch (\ReflectionException) {
                // Skip properties that don't exist
                continue;
            }
        }

        return $instance;
    }
}
