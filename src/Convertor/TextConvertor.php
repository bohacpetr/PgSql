<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

class TextConvertor implements ITypeConvertor
{

    public function fromString(?string $stringValue)
    {
        return $stringValue !== null ? (string)$stringValue : null;
    }

    public function toString($value): ?string
    {
        return $value !== null ? (string)$value : null;
    }
}
