<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Symfony\Messenger\Transport;

use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

final class RedisJsonTransportFactory implements TransportFactoryInterface
{
    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        return new RedisJsonTransport($serializer, $dsn);
    }

    public function supports(string $dsn, array $options): bool
    {
        return str_starts_with($dsn, 'redis-json://');
    }
}

