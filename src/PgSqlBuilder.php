<?php

declare(strict_types=1);

namespace bohyn\PgSql;

class PgSqlBuilder
{

    /** @var PgSqlConnection */
    private $conn;

    public function __construct(PgSqlConnection $conn)
    {
        $this->conn = $conn;
    }

    /**
     * @param string $table
     * @param mixed[] $predicates [columnName => value]
     * @return string
     */
    public function buildSelect(string $table, array &$predicates = []): string
    {
        $table = $this->escapeTableName($table);
        $where = $this->buildWhere($predicates);

        return sprintf('SELECT * FROM %s%s', $table, $where);
    }

    /**
     * @param string $table
     * @param mixed[] $values [columnName => value]
     * @return string
     */
    public function buildInsert(string $table, array $values): string
    {
        $columnNames = array_map(
            function (string $columnName): string {
                return $this->conn->escapeIdentifier($columnName);
            },
            array_keys($values)
        );

        $i = 1;
        $valuePlaceholders = array_map(
            static function () use (&$i): string {
                return '$' . $i++;
            },
            $values
        );

        $table = $this->escapeTableName($table);

        return sprintf(
            'INSERT INTO %s (%s) VALUES (%s) RETURNING *',
            $table,
            implode(', ', $columnNames),
            implode(', ', $valuePlaceholders)
        );
    }

    /**
     * @param string $table
     * @param mixed[] $predicated [columnName => value]
     * @return string
     */
    public function buildDelete(string $table, array $predicated): string
    {
        $table = $this->conn->escapeIdentifier($table);
        $where = $this->buildWhere($predicated);

        return sprintf('DELETE FROM %s%s RETURNING *', $table, $where);
    }

    /**
     * @param string $table
     * @param mixed[] $values
     * @param mixed[] $predicates
     * @return string
     */
    public function buildUpdate(string $table, array $values, array &$predicates): string
    {
        $set = [];
        $i = 1;

        foreach (array_keys($values) as $columnName) {
            $ident = $this->conn->escapeIdentifier($columnName);
            $set[] = sprintf('%s = $%d', $ident, $i);
            $i++;
        }

        $where = $this->buildWhere($predicates, count($set));

        $predicates = array_merge(array_values($values), $predicates);

        return sprintf(
            'UPDATE %s SET %s%s RETURNING *',
            $this->conn->escapeIdentifier($table),
            implode(', ', $set),
            $where
        );
    }

    /**
     * @param mixed[] $predicates
     * @param int $offset
     * @return string
     */
    public function buildWhere(array &$predicates, int $offset = 0): string
    {
        if ($predicates === []) {
            return '';
        }

        $conditions = [];
        $i = $offset + 1;

        foreach ($predicates as $columnName => $param) {
            $ident = $this->conn->escapeIdentifier((string)$columnName);

            if (is_array($param)) {
                $conditions[] = sprintf('%s = ANY ($%d)', $ident, $i);
                $i++;
            } elseif ($param === null) {
                $conditions[] = sprintf('%s IS NULL', $ident);
                unset($predicates[$columnName]);
            } else {
                $conditions[] = "{$ident} = \${$i}";
                $i++;
            }
        }

        $predicates = array_values($predicates);

        return ' WHERE ' . implode(' AND ', $conditions);
    }

    public function escapeTableName(string $table): string
    {
        $tableNameParts = array_map(
            function (string $part): string {
                return $this->conn->escapeIdentifier($part);
            },
            explode('.', $table, 2)
        );

        return implode('.', $tableNameParts);
    }
}
