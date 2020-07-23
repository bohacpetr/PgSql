<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

class IntegerArrayConvertor implements ITypeConvertor
{

    /**
     * @param string|null $stringValue
     * @return int[]|null[]|null
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
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
     * @param mixed[]|null[]|null $values
     * @return string|null
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
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
