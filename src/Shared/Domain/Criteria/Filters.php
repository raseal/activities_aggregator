<?php

declare(strict_types=1);

namespace Shared\Domain\Criteria;

use Shared\Domain\Aggregate\Collection;

final class Filters extends Collection
{
    protected function type(): string
    {
        return Filter::class;
    }
}
