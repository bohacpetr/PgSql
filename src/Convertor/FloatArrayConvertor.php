<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

class FloatArrayConvertor implements ITypeConvertor
{

    /**
     * @param string|null $stringValue
     * @return float[]|null[]|null
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
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
            static function ($value): string {
                return $value !== null ? (string)(float)$value : 'NULL';
            },
            $values
        );

        return '{' . implode(',', $values) . '}';
    }
}
