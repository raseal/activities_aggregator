<?php

declare(strict_types=1);

namespace Shared\Domain\Criteria;

final class Filter
{
    public function __construct(
        private FilterField $field,
        private FilterOperator $operator,
        private FilterValue $value,
        private FilterType $type = FilterType::MUST,
    ) {}

    public function field(): FilterField
    {
        return $this->field;
    }

    public function operator(): FilterOperator
    {
        return $this->operator;
    }

    public function value(): FilterValue
    {
        return $this->value;
    }

    public function type(): FilterType
    {
        return $this->type;
    }
}
