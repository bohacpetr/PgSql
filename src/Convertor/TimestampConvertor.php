<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

use DateTimeImmutable;
use DateTimeInterface;
use Throwable;

class TimestampConvertor implements ITypeConvertor
{

	private const TIMESTAMP_FORMAT = 'Y-m-d H:i:s.u';

	public function fromString(?string $stringValue): ?DateTimeImmutable
	{
		if ($stringValue === null) {
			return null;
		}

		try {
			return DateTimeImmutable::createFromFormat(self::TIMESTAMP_FORMAT, $stringValue);
		} catch (Throwable $e) {
			throw new TypeConversionException(sprintf('Invalid timestamp value "%s"', $stringValue), previous: $e);
		}
	}

	/**
	 * @param DateTimeInterface|string|null $value
	 */
	public function toString(mixed $value): ?string
	{
		if ($value === null) {
			return null;
		}

		try {
			return (new DateTimeImmutable($value))->format(self::TIMESTAMP_FORMAT);
		} catch (Throwable $e) {
			throw new TypeConversionException(sprintf('Invalid date time "%s"', $value), previous: $e);
		}
	}
}
