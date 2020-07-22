<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

class ConvertorCollection
{

    /** @var ITypeConvertor[] */
    private $pgToPhpMap = [];
    /** @var ITypeConvertor[] */
    private $phpToPgMap = [];

    /**
     * @param ITypeConvertor $convertor
     * @param string[] $pgTypes
     * @param string[] $phpTypes
     */
    public function addConvertor(ITypeConvertor $convertor, array $pgTypes, array $phpTypes = []): void
    {
        $this->pgToPhpMap = array_merge(
            $this->pgToPhpMap,
            array_combine(
                $pgTypes,
                array_fill(0, count($pgTypes), $convertor)
            )
        );

        $this->phpToPgMap = array_merge(
            $this->phpToPgMap,
            array_combine(
                $phpTypes,
                array_fill(0, count($phpTypes), $convertor)
            )
        );
    }

    /**
     * @param mixed[] $row
     * @param string[] $fieldTypes
     * @return mixed[]
     */
    public function decodeRow(array $row, array $fieldTypes): array
    {
        foreach ($row as $name => &$value) {
            $type = $fieldTypes[$name] ?? null;

            if (isset($this->pgToPhpMap[$type])) {
                $value = $this->pgToPhpMap[$type]->fromString($value);
            }
        }

        return $row;
    }

    /**
     * @param mixed[] $row
     * @return mixed[]
     */
    public function encodeValues(array $row): array
    {
        foreach ($row as &$value) {
            $type = gettype($value);
            $type = $type === 'object' ? get_class($value) : $type;

            if (isset($this->phpToPgMap[$type])) {
                $value = $this->phpToPgMap[$type]->toString($value);
            }
        }

        return $row;
    }
}
