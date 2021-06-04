<?php

declare(strict_types=1);

namespace bohyn\PgSql;

use bohyn\PgSql\Convertor\ConvertorCollection;

class PgSqlConnection
{

    private string $dsn;
    /** @var resource */
    private $conn;
    private PgSqlBuilder $sqlBuilder;
    private ConvertorCollection $convertors;

    /**
     * @param string $dsn
     * @param ConvertorCollection $convertors
     * @param int $params Currently only value is 0 or PGSQL_CONNECT_FORCE_NEW
     */
    public function __construct(string $dsn, ConvertorCollection $convertors, int $params = 0)
    {
        $this->dsn = $dsn;
        $conn = @pg_connect($this->dsn, $params);

        if (!is_resource($conn)) {
            throw new PgSqlException('Connect failed');
        }

        $this->conn = $conn;
        $this->sqlBuilder = new PgSqlBuilder($this);
        $this->convertors = $convertors;
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
            $params = $this->convertors->encodeValues($params);
            // @ is to prevent error handler intervention
            $result = @pg_query_params($this->conn, $query, $params);
        } else {
            // @ is to prevent error handler intervention
            $result = @pg_query($this->conn, $query);
        }

        if (is_resource($result)) {
            return new PgSqlStatement($result, $this->convertors);
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

        return $this->query($sql, $predicates);
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

        return $this->query($sql, $predicates);
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
            return new PgSqlStatement($result, $this->convertors);
        }

        $query = sprintf('EXECUTE %s', $this->escapeIdentifier($name));
        $this->throwLastError($query, $params);
    }

    /**
     * Escapes a identifier (e.g. table, field names) for querying the database. It returns an escaped identifier string
     * for PostgreSQL server. PgSqlConnection::escapeIdentifier() adds double quotes before and after data. Users should
     * not add double quotes. Use of this function is recommended for identifier parameters in query. For SQL literals
     * (i.e. parameters except bytea), PgSqlConnection::escapeLiteral() or PgSqlConnection::escapeString() must be used.
     * For bytea type fields, PgSqlConnection::escapeBytea() must be used instead.
     *
     * @param string $value
     * @return string
     */
    public function escapeIdentifier(string $value): string
    {
        return pg_escape_identifier($this->conn, $value);
    }

    /**
     * Escapes a literal for querying the PostgreSQL database. It returns an escaped literal in the PostgreSQL format.
     * Adds quotes before and after data. Users should not add quotes. Use of this function is recommended instead of
     * PgSqlConnection::escapeString(). If the type of the column is bytea, PgSqlConnection::escapeBytea() must be used
     * instead. For escaping identifiers (e.g. table, field names), PgSqlConnection::escapeIdentifier() must be used.
     *
     * @param string $value
     * @return string
     */
    public function escapeLiteral(string $value): string
    {
        return pg_escape_literal($this->conn, $value);
    }

    /**
     * Escapes a string for querying the database. It returns an escaped string in the PostgreSQL format without quotes.
     * PgSqlConnection::escapeLiteral() is more preferred way to escape SQL parameters for PostgreSQL. addslashes() must
     * not be used with PostgreSQL. If the type of the column is bytea, PgSqlConnection::escapeBytea() must be used
     * instead. PgSqlConnection::escapeIdentifier() must be used to escape identifiers (e.g. table names, field names)
     *
     * @param string $data
     * @return string
     */
    public function escapeString(string $data): string
    {
        return pg_escape_string($this->conn, $data);
    }

    /**
     * Escape a string for insertion into a bytea field
     *
     * @param string $binary
     * @return string
     */
    public function escapeBytea(string $binary): string
    {
        return pg_escape_bytea($this->conn, $binary);
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
        /** @var array|false $notify */

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

        if ($socket === false) {
            $this->throwLastError('Invalid socket');
        }

        $read = [$socket];
        $write = $except = [];
        $sec = (int)($timeout / 1000000);
        $usec = $timeout - $sec * 1000000;
        stream_select($read, $write, $except, $sec, $usec);
    }

    /**
     * @param string $query
     * @param mixed[] $params
     * @throws PgSqlException
     */
    private function throwLastError(string $query, array $params = []): void
    {
        throw new PgSqlException(
            pg_last_error($this->conn)
            . PHP_EOL
            . $query
            . PHP_EOL
            . var_export($params, true)
        );
    }
}
