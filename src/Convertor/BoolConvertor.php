<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

class BoolConvertor implements ITypeConvertor
{

    /**
     * @param string|null $stringValue
     * @return bool|null
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    public function fromString(?string $stringValue)
    {
        if ($stringValue === null) {
            return null;
        }

        if ($stringValue !== 't' && $stringValue !== 'f') {
            throw new TypeConversionException('Unexpected value, expecting "t" or "f"');
        }

        return $stringValue === 't';
    }

    /**
     * @param bool|null $value
     * @return string|null
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function toString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value === true ? 't' : 'f';
        }

        throw new TypeConversionException('Unexpected value, expecting boolean');
    }
}
