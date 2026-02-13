<?php

declare(strict_types=1);

namespace Ingestor\Domain\ValueObject\Zone;

use Ingestor\Domain\Exception\EmptyZoneName;
use Shared\Domain\ValueObject\StringValueObject;

final readonly class ZoneName extends StringValueObject
{
    public function __construct(string $title)
    {
        if (empty(trim($title))) {
            throw new EmptyZoneName();
        }

        parent::__construct($title);
    }
}

