<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

use DateTimeImmutable;
use DateTimeInterface;
use Throwable;

class TimestampConvertor implements ITypeConvertor
{

    private const TIMESTAMP_FORMAT = 'Y-m-d H:i:s.uP';

    /**
     * @param string|null $stringValue
     * @return DateTimeImmutable|null
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
     */
    public function toString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format(self::TIMESTAMP_FORMAT);
        }

        try {
            return (new DateTimeImmutable($value))->format(self::TIMESTAMP_FORMAT);
        } catch (Throwable $e) {
            throw new TypeConversionException(sprintf('Invalid date time "%s"', $value));
        }
    }
}
