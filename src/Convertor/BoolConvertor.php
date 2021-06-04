<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

class BoolConvertor implements ITypeConvertor
{

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    public function fromString(?string $stringValue): ?bool
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
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function toString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? 't' : 'f';
        }

        throw new TypeConversionException('Unexpected value, expecting boolean');
    }
}
