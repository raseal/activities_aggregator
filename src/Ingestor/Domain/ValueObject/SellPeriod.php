<?php

declare(strict_types=1);

namespace Ingestor\Domain\ValueObject;

final readonly class SellPeriod
{
    private function __construct(
        private \DateTimeImmutable $sellFrom,
        private \DateTimeImmutable $sellTo,
    ) {
        $this->ensureValidSellPeriod();
    }

    public static function fromDates(\DateTimeImmutable $sellFrom, \DateTimeImmutable $sellTo): self
    {
        return new self($sellFrom, $sellTo);
    }

    private function ensureValidSellPeriod(): void
    {
        if ($this->sellTo < $this->sellFrom) {
            throw new \DomainException(
                sprintf(
                    'Sale end date (%s) cannot be before sale start date (%s)',
                    $this->sellTo->format('Y-m-d H:i:s'),
                    $this->sellFrom->format('Y-m-d H:i:s')
                )
            );
        }
    }

    public function sellFrom(): \DateTimeImmutable
    {
        return $this->sellFrom;
    }

    public function sellTo(): \DateTimeImmutable
    {
        return $this->sellTo;
    }
}

