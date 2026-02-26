<?php

declare(strict_types=1);

namespace Shared\Domain\Criteria;

enum FilterOperator: string
{
    case EQ = '=';
    case GT = '>';
    case LT = '<';
    case GTE = '>=';
    case LTE = '<=';
    case CONTAINS = 'contains';
    case IN = 'in';
    case RANGE = 'range';
}

