<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

class FloatConvertor implements ITypeConvertor
{

    /**
     * @param string|null $stringValue
     * @return float|null
     */
    public function fromString(?string $stringValue)
    {
        return $stringValue !== null ? (float)$stringValue : null;
    }

    /**
     * @param float|null $value
     * @return string|null
     */
    public function toString($value): ?string
    {
        return $value !== null ? (string)$value : null;
    }
}
