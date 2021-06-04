<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

class FloatArrayConvertor implements ITypeConvertor
{

	private FloatConvertor $floatConvertor;

	public function __construct()
	{
		$this->floatConvertor = new FloatConvertor();
	}

    /**
     * @param string|null $stringValue
     * @return float[]|null[]|null
     */
    public function fromString(?string $stringValue): ?array
    {
        if ($stringValue === null) {
            return null;
        }

        $values = explode(',', trim($stringValue, '{}'));

        return array_map(
            function ($value): ?float {
                return $this->floatConvertor->fromString($value);
            },
            $values
        );
    }

    /**
     * @param float[]|null[]|null $values
     * @return string|null
     */
    public function toString(mixed $values): ?string
    {
        if ($values === null) {
            return null;
        }

        $values = array_map(
            static function ($value): string {
                return $this->floatConvertor->toString($value);
            },
            $values
        );

        return '{' . implode(',', $values) . '}';
    }
}
