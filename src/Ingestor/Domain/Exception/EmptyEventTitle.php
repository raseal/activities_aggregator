<?php

declare(strict_types=1);

namespace Ingestor\Domain\Exception;

use Shared\Domain\Exception\DomainError;

final class EmptyEventTitle extends DomainError
{
    public function errorCode(): string
    {
        return 'empty_event_title';
    }

    public function errorMessage(): string
    {
        return 'The event title cannot be empty';
    }
}
