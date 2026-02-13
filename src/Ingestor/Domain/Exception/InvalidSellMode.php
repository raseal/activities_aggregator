<?php

declare(strict_types=1);

namespace Ingestor\Domain\Exception;

use Shared\Domain\Exception\DomainError;
use function sprintf;

final class InvalidSellMode extends DomainError
{
    public function __construct(
        private readonly string $sellMode,
    ) {
        parent::__construct();
    }

    public function errorCode(): string
    {
        return 'invalid_sell_mode';
    }

    public function errorMessage(): string
    {
        return sprintf(
            'Invalid sell mode: "%s"',
            $this->sellMode,
        );
    }
}
