<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

class IntegerArrayConvertor implements ITypeConvertor
{

    /**
     * @param string|null $stringValue
     * @return int[]|null[]|null
     */
    public function fromString(?string $stringValue)
    {
        if ($stringValue === null) {
            return null;
        }

        $values = explode(',', trim($stringValue, '{}'));

        return array_map(
            static function (string $value): ?int {
                return strtoupper($value) !== 'NULL' ? (int)$value : null;
            },
            $values
        );
    }

    /**
     * @param int[]|null[]|null $values
     * @return string|null
     */
    public function toString($values): ?string
    {
        if ($values === null) {
            return null;
        }

        $values = array_map(
            static function ($value) {
                return $value !== null ? (int)$value : 'NULL';
            },
            $values
        );

        return '{' . implode(',', $values) . '}';
    }
}
