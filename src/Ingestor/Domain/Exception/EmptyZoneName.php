<?php

declare(strict_types=1);

namespace Ingestor\Domain\Exception;

use Shared\Domain\Exception\DomainError;

final class EmptyZoneName extends DomainError
{
    public function errorCode(): string
    {
        return 'empty_zone_name';
    }

    public function errorMessage(): string
    {
        return 'The zone name cannot be empty';
    }
}
