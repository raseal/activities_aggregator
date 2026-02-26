<?php

declare(strict_types=1);

namespace Shared\Domain\Criteria;

enum FilterType: string
{
    case MUST = 'must';
    case MUST_NOT = 'must_not';
    case SHOULD = 'should';
}
