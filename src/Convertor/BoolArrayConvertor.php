<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

class BoolArrayConvertor implements ITypeConvertor
{

    /** @var BoolConvertor */
    private $boolConvertor;

    public function __construct()
    {
        $this->boolConvertor = new BoolConvertor();
    }

    /**
     * @param string|null $stringValue
     * @return bool[]|null[]|null
     */
    public function fromString(?string $stringValue)
    {
        if ($stringValue === null) {
            return null;
        }

        $values = explode(',', trim($stringValue, '{}'));

        return array_map(
            static function ($value): ?bool {
                if (strtoupper($value) === 'NULL') {
                    return null;
                }

                return $this->boolConvertor->fromString($value);
            },
            $values
        );
    }

    /**
     * @param bool[]|null[]|null $values
     * @return string|null
     */
    public function toString($values): ?string
    {
        if ($values === null) {
            return null;
        }

        $values = array_map(
            static function ($value): string {
                if ($value === null) {
                    return 'NULL';
                }

                return $this->boolConvertor->toString($value);
            },
            $values
        );

        return '{' . implode(',', $values) . '}';
    }
}
