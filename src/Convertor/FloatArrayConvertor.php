<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

class FloatArrayConvertor implements ITypeConvertor
{

    /**
     * @param string|null $stringValue
     * @return float[]|null[]|null
     */
    public function fromString(?string $stringValue)
    {
        if ($stringValue === null) {
            return null;
        }

        $values = explode(',', trim($stringValue, '{}'));

        return array_map(
            static function ($value): ?float {
                return strtoupper($value) !== 'NULL' ? (float)$value : null;
            },
            $values
        );
    }

    /**
     * @param float[]|null[]|null $values
     * @return string|null
     */
    public function toString($values): ?string
    {
        if ($values === null) {
            return null;
        }

        $values = array_map(
            static function ($value): string {
                return $value !== null ? (string)(float)$value : 'NULL';
            },
            $values
        );

        return '{' . implode(',', $values) . '}';
    }
}
