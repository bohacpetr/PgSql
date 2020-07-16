<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

use bohyn\PgSql\DataType\Point;

class PointConvertor implements ITypeConvertor
{

    /** @var FloatConvertor */
    private $floatConvertor;

    public function __construct()
    {
        $this->floatConvertor = new FloatConvertor();
    }

    public function fromString(?string $stringValue)
    {
        if ($stringValue === null) {
            return null;
        }

        [$x, $y] = explode(',', substr($stringValue, 1, -1));
        $x = $this->floatConvertor->fromString($x);
        $y = $this->floatConvertor->fromString($y);

        if ($x === null || $y === null) {
            throw new TypeConversionException(
                'Invalid point coordinates given: ' . var_export(['x' => $x, 'y' => $y])
            );
        }

        return new Point($x, $y);
    }

    public function toString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof Point) {
            $type = gettype($value);
            $type = $type === 'object' ? get_class($value) : $type;

            throw new TypeConversionException(
                sprintf('Value must be of type "%s" but "%s" given', Point::class, $type)
            );
        }

        return sprintf('(%s,%s)', $value->getX(), $value->getY());
    }
}
