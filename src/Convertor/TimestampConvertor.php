<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

use bohyn\PgSql\PgSqlInternalError;
use DateTimeImmutable;
use DateTimeInterface;
use Throwable;

class TimestampConvertor implements ITypeConvertor
{

	private const TIMESTAMP_FORMAT = 'Y-m-d H:i:s.u';

	/**
	 * @param string|null $stringValue
	 * @return DateTimeImmutable|null
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
	 */
	public function fromString(?string $stringValue)
	{
		if ($stringValue === null) {
			return null;
		}

		try {
			return DateTimeImmutable::createFromFormat(self::TIMESTAMP_FORMAT, $stringValue);
		} catch (Throwable $e) {
			throw new TypeConversionException(sprintf('Invalid timestamp value "%s"', $stringValue));
		}
	}

	/**
	 * @param DateTimeInterface|string|null $value
	 * @return string|null
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
	 */
	public function toString($value): ?string
	{
		if ($value === null) {
			return null;
		}

		if (!$value instanceof DateTimeInterface) {
			try {
				$value = new DateTimeImmutable($value);
			} catch (Throwable $e) {
				throw new TypeConversionException(sprintf('Invalid date time "%s"', $value));
			}
		}

		/** @var string|false $timestamp */
		$timestamp = $value->format(self::TIMESTAMP_FORMAT);

		if ($timestamp === false) {
			throw new PgSqlInternalError('Invalid timestamp format');
		}

		return $timestamp;
	}
}
