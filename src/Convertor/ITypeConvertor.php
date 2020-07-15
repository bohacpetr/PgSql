<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

interface ITypeConvertor
{

    /**
     * @param string|null $stringValue
     * @return mixed|null
     */
    public function fromString(?string $stringValue);

    /**
     * @param mixed|null $value
     * @return string|null
     */
    public function toString($value): ?string;
}
