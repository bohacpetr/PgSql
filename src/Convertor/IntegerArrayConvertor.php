<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

class IntegerArrayConvertor implements ITypeConvertor
{

	private IntegerConvertor $integerConvertor;

	public function __construct() {
		$this->integerConvertor = new IntegerConvertor();
	}

    /**
     * @param string|null $stringValue
     * @return int[]|null[]|null
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    public function fromString(?string $stringValue): ?array
    {
        if ($stringValue === null) {
            return null;
        }

        $values = explode(',', trim($stringValue, '{}'));

        return array_map(
            static function (string $value): ?int {
                return $this->integerConvertor->fromString($value);
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
                return $this->integerConvertor->toString($value);
            },
            $values
        );

        return '{' . implode(',', $values) . '}';
    }
}
