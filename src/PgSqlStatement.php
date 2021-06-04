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
    private array $fieldTypes = [];
    /** Default class name for fetchObject */
    private ?string $className;
    private ConvertorCollection $convertors;

    /**
     * @param resource $result
     * @param ConvertorCollection $convertors
     * @internal To be used only by PgSqlConnection
     */
    public function __construct($result, ConvertorCollection $convertors)
    {
    	$this->className = null;
        $this->result = $result;
        $this->convertors = $convertors;

        for ($i = 0, $count = pg_num_fields($this->result); $i < $count; $i++) {
            $type = pg_field_type($this->result, $i);
            $this->fieldTypes[pg_field_name($this->result, $i)] = $type;
        }
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
     * @return mixed|false first column first row
     */
    public function fetchColumn(): mixed
	{
		if(pg_num_rows($this->result) === 0) {
			return null;
		}

        $value = pg_fetch_result($this->result, 0, 0);

        if ($value === null) {
        	return null;
        }

		return $this->convertors->decodeRow($value, array_shift($this->fieldTypes));
    }

	/**
	 * @param int|null $rowNum Row number to fetch.
	 * @return bool|mixed[]
	 */
    public function fetchAssoc(?int $rowNum = null): array|bool
	{
        $row = pg_fetch_assoc($this->result, $rowNum);

        if ($row) {
            return $this->convertors->decodeRow($row, $this->fieldTypes);
        }

        return $row;
    }

	/**
	 * @param string|null $className Target class to fetch row into.
	 * @param int|null $row Row number to fetch.
	 * @return bool|mixed[]
	 */
    public function fetchObject(?string $className = null, ?int $row = null): object|bool
	{
        /** @var object|bool $row */
        $row = pg_fetch_assoc($this->result, $row);
        $row = pg_fetch_object($this->result, $row);

        if ($row === false) {
            return false;
        }

        $row = $this->convertors->decodeRow($row, $this->fieldTypes);
        $className = ($className ?: $this->className) ?: 'stdClass';

            $class = new $className();

            foreach($row as $column => $value) {
                $class->$column = $value;
            }

            $row = $class;

        return $row;
    }

    /**
	 * @param string|null $className Target class to fetch rows into.
     * @return mixed[][]
     */
    public function fetchAll(?string $className = null): array
    {
        $this->className = $className;
        $result = [];

        foreach ($this as $row) {
            $result[] = $row;
        }

        return $result;
    }

    /**
     * @return int Returns the number of rows affected by INSERT/UPDATE/DELETE.
     */
    public function affectedRows(): int
    {
        return pg_affected_rows($this->result);
    }

    /**
     * @return int will return the number of rows in a PostgreSQL result resource. Use only on SELECT.
     */
    public function numRows(): int
    {
        return pg_numrows($this->result);
    }

	/**
	 * @param int $offset Move cursot to row $offset. First row is 0.
	 * @return bool
	 */
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
