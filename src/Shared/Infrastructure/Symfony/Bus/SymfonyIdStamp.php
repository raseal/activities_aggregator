<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Symfony\Bus;

use Shared\Domain\ValueObject\Uuid;
use Symfony\Component\Messenger\Stamp\StampInterface;

final class SymfonyIdStamp implements StampInterface
{
    public function __construct(
        private string $id,
    ) {
    }

    public static function create(): self
    {
        return new self(
            str_replace('-', '', Uuid::random()->value())
        );
    }

    public function value(): string
    {
        return $this->id;
    }
}
