<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

class ConvertorCollection
{

    /** @var ITypeConvertor[] */
    private $pgToPhpMap = [];
    /** @var ITypeConvertor[] */
    private $phpToPgMap = [];

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
}
