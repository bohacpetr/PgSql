<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

class JsonConvertor implements ITypeConvertor
{

    /**
     * @param string|null $stringValue
     * @return mixed|null
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    public function fromString(?string $stringValue)
    {
        if ($stringValue === null) {
            return null;
        }

        return json_decode($stringValue);
    }

    /**
     * @param mixed|null $value
     * @return string|null
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function toString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $json = json_encode($value);

        if ($json === false) {
            throw new TypeConversionException('Value cannot be serialized to JSON');
        }

        return $json;
    }
}
