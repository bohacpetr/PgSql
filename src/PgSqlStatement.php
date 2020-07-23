<?php

declare(strict_types=1);

namespace bohyn\PgSql;

use bohyn\PgSql\Convertor\ConvertorCollection;
use Generator;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<mixed>
 */
class PgSqlStatement implements IteratorAggregate
{

    /** @var resource */
    private $result;

    /** @var string[] */
    private $fieldTypes = [];

    /**
     * Default class name for fetchObject
     *
     * @var string|null
     */
    private $className;
    /** @var ConvertorCollection */
    private $convertors;

    /**
     * @param resource $result
     * @param ConvertorCollection $convertors
     * @internal To be used only by PgSqlConnection
     */
    public function __construct($result, ConvertorCollection $convertors)
    {
        $this->result = $result;

        for ($i = 0, $count = pg_num_fields($this->result); $i < $count; $i++) {
            $type = pg_field_type($this->result, $i);
            $this->fieldTypes[pg_field_name($this->result, $i)] = $type;
        }

        $this->convertors = $convertors;
    }

    public function __destruct()
    {
        pg_free_result($this->result);
    }

    public function setClassName(string $className): void
    {
        $this->className = $className;
    }

    /**
     * @return mixed first column first row || false
     */
    public function fetchColumn()
    {
        $value = pg_num_rows($this->result) !== 0 ? pg_fetch_result($this->result, 0, 0) : null;

        if ($value !== null) {
            return $this->convertors->decodeRow($value, array_shift($this->fieldTypes));
        }

        return $value;
    }

    public function affectedtRows(): int
    {
        return pg_affected_rows($this->result);
    }

    /**
     * @param int $rowNum Row number to fetch
     * @return bool|mixed[]
     */
    public function fetchAssoc(?int $rowNum = null)
    {
        $row = pg_fetch_assoc($this->result, $rowNum);

        if ($row) {
            return $this->convertors->encodeValues($row);
        }

        return $row;
    }

    /**
     * @param int $row Row number to fetch
     * @return bool|mixed[]
     */
    public function fetchObject(?string $className = null, ?int $row = null)
    {
        $className = $className ?: $this->className;

        /** @var object|bool $row */
        $row = $className !== null
            ? pg_fetch_object($this->result, $row, $className)
            : pg_fetch_object($this->result, $row);

        if ($row === false) {
            return false;
        }

        return $this->convertors->decodeRow($row, $this->fieldTypes);
    }

    /**
     * @return mixed[][]
     */
    public function fetchAll(): array
    {
        $result = [];

        foreach ($this as $row) {
            $result[] = $row;
        }

        return $result;
    }

    /**
     * @return int will return the number of rows in a PostgreSQL result resource.
     */
    public function numRows(): int
    {
        return pg_numrows($this->result);
    }

    public function seek(int $offset): bool
    {
        return pg_result_seek($this->result, $offset);
    }

    public function getIterator(): Generator
    {
        $this->seek(0);

        while (($row = $this->className ? $this->fetchObject() : $this->fetchAssoc())) {
            yield $row;
        }
    }
}
