<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

interface ITypeConvertor
{

	public function fromString(?string $stringValue): mixed;

	public function toString(mixed $value): ?string;
}
