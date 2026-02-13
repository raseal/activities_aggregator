<?php

declare(strict_types=1);

namespace Ingestor\Domain\ValueObject;

use Ingestor\Domain\Exception\EmptyEventTitle;
use Shared\Domain\ValueObject\StringValueObject;

final readonly class EventTitle extends StringValueObject
{
    public function __construct(string $title)
    {
        if (empty(trim($title))) {
            throw new EmptyEventTitle();
        }

        parent::__construct($title);
    }
}

