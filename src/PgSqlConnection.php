<?php

declare(strict_types=1);

namespace bohyn\PgSql;

class PgSqlConnection
{

    /** @var string */
    private $dsn;
    /** @var resource */
    private $conn;
    /** @var PgSqlBuilder */
    private $sqlBuilder;

    /**
     * @param string $dsn
     * @param int $params
     * @throws PgSqlException
     */
    public function __construct(string $dsn, int $params = 0)
    {
        $this->dsn = $dsn;
        $this->conn = @pg_connect($this->dsn, $params);

        if (!is_resource($this->conn)) {
            $this->throwLastError('Connect');
        }

        $this->sqlBuilder = new PgSqlBuilder($this);
    }

    public function __destruct()
    {
        if (is_resource($this->conn)) {
            pg_close($this->conn);
        }
    }

    public function getDsn(): string
    {
        return $this->dsn;
    }

    /**
     * @return PgSqlStatement
     * @throws PgSqlException
     */
    public function begin(): PgSqlStatement
    {
        return $this->query('BEGIN');
    }

    /**
     * @return PgSqlStatement
     * @throws PgSqlException
     */
    public function commit(): PgSqlStatement
    {
        return $this->query('COMMIT');
    }

    /**
     * @return PgSqlStatement
     * @throws PgSqlException
     */
    public function rollback(): PgSqlStatement
    {
        return $this->query('ROLLBACK');
    }

    /**
     * @return int PGSQL_TRANSACTION_IDLE|PGSQL_TRANSACTION_ACTIVE|PGSQL_TRANSACTION_INTRANS
     *         |PGSQL_TRANSACTION_INERROR|PGSQL_TRANSACTION_UNKNOWN|PGSQL_TRANSACTION_ACTIVE
     */
    public function transactionStatus(): int
    {
        return pg_transaction_status($this->conn);
    }

    /**
     * @param string $query
     * @param mixed[] $params
     * @return PgSqlStatement
     */
    public function query(string $query, array $params = []): PgSqlStatement
    {
        if ($params !== []) {
            $params = Helper::encodeParams($this, $params);
            // @ is to prevent error handler intervention
            $result = @pg_query_params($this->conn, $query, $params);
        } else {
            // @ is to prevent error handler intervention
            $result = @pg_query($this->conn, $query);
        }

        if (is_resource($result)) {
            return new PgSqlStatement($result);
        }

        $this->throwLastError($query, $params);
    }

    /**
     * @param string $tableName
     * @param mixed[] $predicates
     * @return PgSqlStatement
     * @throws PgSqlException
     */
    public function select(string $tableName, array $predicates): PgSqlStatement
    {
        $sql = $this->sqlBuilder->buildSelect($tableName, $predicates);

        return $this->query($sql);
    }

    /**
     * @param string $table
     * @param mixed[] $values
     * @return PgSqlStatement
     * @throws PgSqlException
     */
    public function insert(string $table, array $values): PgSqlStatement
    {
        $sql = $this->sqlBuilder->buildInsert($table, $values);

        return $this->query($sql, $values);
    }

    /**
     * @param string $table
     * @param mixed[] $set
     * @param mixed[] $predicates
     * @return PgSqlStatement
     * @throws PgSqlException
     */
    public function update(string $table, array $set, array $predicates): PgSqlStatement
    {
        $sql = $this->sqlBuilder->buildUpdate($table, $set, $predicates);
        $params = $set + $predicates;

        return $this->query($sql, $params);
    }

    /**
     * @param string $table
     * @param mixed[] $predicates
     * @return PgSqlStatement
     * @throws PgSqlException
     */
    public function delete(string $table, array $predicates): PgSqlStatement
    {
        $sql = $this->sqlBuilder->buildDelete($table, $predicates);

        return $this->query($sql);
    }

    /**
     * @param string $name
     * @param string $query
     * @return PgSqlPreparedStatement
     * @throws PgSqlException
     */
    public function prepare(string $name, string $query): PgSqlPreparedStatement
    {
        // @ is to prevent error handler intervention
        $result = @pg_prepare($this->conn, $name, $query);

        if (is_resource($result)) {
            return new PgSqlPreparedStatement($this, $name);
        }

        $query = sprintf('PREPARE %s AS %s', $this->escapeIdentifier($name), $query);
        $this->throwLastError($query);
    }

    /**
     * @param string $name
     * @param mixed[] $params
     * @return PgSqlStatement
     * @throws PgSqlException
     */
    public function execute(string $name, array $params = []): PgSqlStatement
    {
        // @ is to prevent error handler intervention
        $result = @pg_execute($this->conn, $name, $params);

        if (is_resource($result)) {
            return new PgSqlStatement($result);
        }

        $query = sprintf('EXECUTE %s', $this->escapeIdentifier($name));
        $this->throwLastError($query, $params);
    }

    public function escapeIdentifier(string $value): string
    {
        return pg_escape_identifier($this->conn, $value);
    }

    /**
     * @param string $value
     * @return string
     */
    public function escapeLiteral(string $value): string
    {
        return pg_escape_literal($this->conn, $value);
    }

    /**
     * escapes a string for querying the database. It returns an escaped string in the PostgreSQL format without quotes.
     *
     * @param string $data
     * @return string
     */
    public function escapeString(string $data): string
    {
        return pg_escape_string($this->conn, $data);
    }

    public function ping(): bool
    {
        $result = @pg_ping($this->conn);

        if ($result === true) {
            return $result;
        }

        $this->throwLastError('Ping');
    }

    /**
     * @param int $timeout
     * @return string[]
     */
    public function getNotify(int $timeout = 0): array
    {
        $notify = pg_get_notify($this->conn, PGSQL_ASSOC);

        if (is_array($notify)) {
            return $notify;
        }

        if ($notify === false && $timeout === 0) {
            return [];
        }

        $this->socketBlock($timeout);

        return $this->getNotify();
    }

    /**
     * @param int $timeout uSec to wait
     */
    private function socketBlock(int $timeout): void
    {
        $socket = pg_socket($this->conn);
        $read = [$socket];
        $write = $except = [];
        $sec = (int)($timeout / 1000000);
        $usec = $timeout - $sec * 1000000;
        stream_select($read, $write, $except, $sec, $usec);
    }

    /**
     * @param string $query
     * @param mixed[]|null $params
     * @throws PgSqlException
     */
    private function throwLastError(string $query, array $params = []): void
    {
        throw new PgSqlException(
            is_resource($this->conn) ? pg_last_error($this->conn) : 'Connection error'
                . PHP_EOL
                . $query
                . PHP_EOL
                . var_export($params, true)
        );
    }
}
