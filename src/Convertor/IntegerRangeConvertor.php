<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

use bohyn\PgSql\DataType\IntegerRange;
use bohyn\PgSql\DataType\RangeException;

class IntegerRangeConvertor implements ITypeConvertor
{

    /**
     * @param string|null $stringValue
     * @return IntegerRange|null
     * @throws RangeException
     */
    public function fromString(?string $stringValue)
    {
        if ($stringValue === null) {
            return null;
        }

        [$from, $to] = explode(',', trim($stringValue, '()[]'));

        $from = $from === '' ? IntegerRange::INFINITY : (int)$from;
        $to = $to === '' ? IntegerRange::INFINITY : (int)$to;

        return new IntegerRange(
            $from,
            $to,
            substr($stringValue, 0, 1) . substr($stringValue, -1)
        );
    }

    /**
     * @param IntegerRange|null $value
     * @return string|null
     */
    public function toString($value): ?string
    {
        $from = $value->getFrom() !== null ? (int)$value->getFrom() : "";
        $to = $value->getTo() !== null ? (int)$value->getTo() : "";
        $leftBracket = $value->isFromInclusive() === true ? '(' : '[';
        $rightBracket = $value->isToInclusive() === true ? ')' : ']';

        return $leftBracket . $from . ',' . $to . $rightBracket;
    }
}
