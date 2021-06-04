<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

class JsonConvertor implements ITypeConvertor
{

	public function fromString(?string $stringValue): mixed
	{
		if ($stringValue === null) {
			return null;
		}

		return json_decode($stringValue, flags: JSON_THROW_ON_ERROR);
	}

	public function toString(mixed $value): ?string
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
