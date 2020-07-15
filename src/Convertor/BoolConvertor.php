<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

class BoolConvertor implements ITypeConvertor
{

    /**
     * @param string|null $stringValue
     * @return bool|null
     */
    public function fromString(?string $stringValue)
    {
        if ($stringValue === null) {
            return null;
        }

        if (preg_match('~^[tf]$~', $stringValue) === 0) {
            throw new TypeConversionException('Unexpected value, expecting "t" or "f"');
        }

        return $stringValue === 't';
    }

    /**
     * @param bool|null $value
     * @return string|null
     */
    public function toString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value === true ? 't' : 'f';
        }

        switch ($value) {
            case '0':
            case 'f':
            case 'false':
                return 'f';
            case '1':
            case 't':
            case 'true':
                return 't';
        }

        throw new TypeConversionException('Unexpected value, expecting boolean');
    }
}
