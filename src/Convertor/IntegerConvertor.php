<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

class IntegerConvertor implements ITypeConvertor
{

    /**
     * @param string|null $stringValue
     * @return int|null
     */
    public function fromString(?string $stringValue)
    {
        if (!is_numeric($stringValue) && $stringValue !== null) {
            throw new TypeConversionException('Non-numeric input');
        }

        return $stringValue !== null ? (int)$stringValue : null;
    }

    /**
     * @param int|null $value
     * @return string|null
     */
    public function toString($value): ?string
    {
        if (!is_numeric($value) && $value !== null) {
            throw new TypeConversionException('Non-numeric input');
        }

        return $value !== null ? (string)(int)$value : null;
    }
}
