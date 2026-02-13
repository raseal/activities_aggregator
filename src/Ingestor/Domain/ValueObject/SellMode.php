<?php

declare(strict_types=1);

namespace Ingestor\Domain\ValueObject;

use Ingestor\Domain\Exception\InvalidSellMode;

enum SellMode: string
{
    case ONLINE = 'online';
    case OFFLINE = 'offline';

    public static function fromString(string $value): self
    {
        return self::tryFrom($value) ?? throw new InvalidSellMode($value);
    }

    public function isOnline(): bool
    {
        return $this === self::ONLINE;
    }

    public function isOffline(): bool
    {
        return $this === self::OFFLINE;
    }
}

