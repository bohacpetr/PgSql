<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

class TextConvertor implements ITypeConvertor
{

	public function fromString(?string $stringValue): ?string
	{
		return $stringValue;
	}

	public function toString(mixed $value): ?string
	{
		return $value !== null ? (string)$value : null;
	}
}
