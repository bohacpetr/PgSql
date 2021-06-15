<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

class TextConvertor implements ITypeConvertor
{

	/**
	 * @param string|null $stringValue
	 * @return string|null
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
	 */
	public function fromString(?string $stringValue)
	{
		return $stringValue;
	}

	/**
	 * @param mixed|null $value
	 * @return string|null
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
	 */
	public function toString($value): ?string
	{
		return $value !== null ? (string)$value : null;
	}
}
