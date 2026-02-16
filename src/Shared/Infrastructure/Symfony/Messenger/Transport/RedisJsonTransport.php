<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Symfony\Messenger\Transport;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * Custom Redis Transport that stores messages as pure JSON without PHP serialization.
 */
final class RedisJsonTransport implements TransportInterface
{
    private \Redis $redis;
    private string $stream;

    public function __construct(
        private readonly SerializerInterface $serializer,
        string $dsn
    ) {
        // Parse DSN: redis://host:port/stream
        $parsed = parse_url($dsn);
        $host = $parsed['host'] ?? 'localhost';
        $port = $parsed['port'] ?? 6379;
        $this->stream = trim($parsed['path'] ?? '/messages', '/');

        $this->redis = new \Redis();
        $this->redis->connect($host, $port);
    }

    public function get(): iterable
    {
        // Read from Redis list (LPOP for FIFO)
        $message = $this->redis->lPop($this->stream);

        if ($message === false || $message === null) {
            return [];
        }

        // Decode from JSON
        $decoded = json_decode($message, true, 512, JSON_THROW_ON_ERROR);

        // Deserialize using our serializer
        $envelope = $this->serializer->decode($decoded);

        return [$envelope];
    }

    public function ack(Envelope $envelope): void
    {
        // For simple implementation, we already removed from list with LPOP
        // In production, you'd want to use Redis Streams for better reliability
    }

    public function reject(Envelope $envelope): void
    {
        // Move to dead letter queue if needed
        // For now, we just acknowledge (remove from queue)
    }

    public function send(Envelope $envelope): Envelope
    {
        // Serialize the envelope
        $encoded = $this->serializer->encode($envelope);

        // Convert to JSON string (no PHP serialize!)
        $jsonMessage = json_encode($encoded, JSON_THROW_ON_ERROR);

        // Push to Redis list (RPUSH for FIFO)
        $this->redis->rPush($this->stream, $jsonMessage);

        return $envelope;
    }
}

