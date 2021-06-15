<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

class FloatConvertor implements ITypeConvertor
{

	/**
	 * @param string|null $stringValue
	 * @return float|null
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
	 */
	public function fromString(?string $stringValue)
	{
		return $stringValue !== null ? (float)$stringValue : null;
	}

	/**
	 * @param float|null $value
	 * @return string|null
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
	 */
	public function toString($value): ?string
	{
		return $value !== null ? (string)$value : null;
	}
}
