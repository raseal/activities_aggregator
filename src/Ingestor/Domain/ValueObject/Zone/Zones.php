<?php

declare(strict_types=1);

namespace Ingestor\Domain\ValueObject\Zone;

use Shared\Domain\Aggregate\Collection;

final class Zones extends Collection
{
    protected function type(): string
    {
        return Zone::class;
    }
}
