<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

use DateTimeImmutable;
use DateTimeInterface;
use Throwable;

class DateConvertor implements ITypeConvertor
{

    private const DATE_FORMAT = 'Y-m-d';

    /**
     * @throws TypeConversionException
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    public function fromString(?string $stringValue): ?DateTimeImmutable
    {
        if ($stringValue === null) {
            return null;
        }

        try {
            $date = new DateTimeImmutable($stringValue);

            return $date->setTime(0, 0, 0, 0);
        } catch (Throwable $e) {
            throw new TypeConversionException(sprintf('Invalid date value "%s"', $stringValue));
        }
    }

    /**
     * @param DateTimeInterface|string|null $value
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function toString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        try {
            return (new DateTimeImmutable($value))->format(self::DATE_FORMAT);
        } catch (Throwable $e) {
            throw new TypeConversionException(sprintf('Invalid date value "%s"', $value));
        }
    }
}
