<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

class IntegerConvertor implements ITypeConvertor
{

	public function fromString(?string $stringValue): ?int
	{
		if ($stringValue === null) {
			return null;
		}

		if (!is_numeric($stringValue)) {
			throw new TypeConversionException('Non-numeric input');
		}

		return (int)$stringValue;
	}

	/**
	 * @param int|null $value
	 */
	public function toString(mixed $value): ?string
	{
		if ($value === null) {
			return null;
		}

		if (!is_numeric($value)) {
			throw new TypeConversionException('Non-numeric input');
		}

		return (string)(int)$value;
	}
}
