<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

class ConvertorCollection
{

    /** @var ITypeConvertor[] */
    private array $pgToPhpMap;
    /** @var ITypeConvertor[] */
    private array $phpToPgMap;

    public function __construct() {
    	$this->pgToPhpMap = [];
    	$this->phpToPgMap = [];
	}

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
     * @param string|string[]|null $row
     * @param string|string[] $fieldTypes
     * @return mixed
     */
    public function decodeRow($row, $fieldTypes)
    {
        if(is_array($row)) {
            foreach ($row as $name => &$value) {
                $type = $fieldTypes[$name] ?? null;

                if (isset($this->pgToPhpMap[$type])) {
                    $value = $this->pgToPhpMap[$type]->fromString($value);
                }
            }
        }
        else {
            if (isset($this->pgToPhpMap[$fieldTypes])) {
                $row = $this->pgToPhpMap[$fieldTypes]->fromString($row);
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
