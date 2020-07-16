<?php

declare(strict_types=1);

namespace bohyn\PgSql;

use PHPUnit\Framework\TestCase;

class PgSqlConnectionTest extends TestCase
{

    private const DSN = 'host=localhost user=postgres password=postgres';

    /** @var PgSqlConnection */
    private $conn;

    public function testConnect(): void
    {
        $conn = new PgSqlConnection(self::DSN, PGSQL_CONNECT_FORCE_NEW);
        $result = $conn->query('SELECT 1');
        $this->assertInstanceOf(PgSqlStatement::class, $result);
        $this->assertEquals(1, $result->numRows());
        $this->assertEquals(1, $result->fetchColumn());

        $this->assertEquals(self::DSN, $conn->getDsn());

        unset($conn);
        gc_collect_cycles();
        // call explicitly garbage collector, because GC may not trigger immediately

        $this->expectException(PgSqlException::class);
        $this->expectExceptionMessage("Connect failed");

        new PgSqlConnection('host=localhost user=nonexisting password=nonexisting');
    }

    public function testQuery(): void
    {
        $result = $this->conn->query('SELECT 1 WHERE 1 = $1', [1]);
        $this->assertInstanceOf(PgSqlStatement::class, $result);
        $this->assertEquals(1, $result->numRows());
        $this->assertEquals(1, $result->fetchColumn());

        $result = $this->conn->query('SELECT 1 WHERE 1 = $1', [2]);
        $this->assertInstanceOf(PgSqlStatement::class, $result);
        $this->assertEquals(0, $result->numRows());
        $this->assertNull($result->fetchColumn());
        $this->assertFalse($result->fetchAssoc());

        $this->expectException(PgSqlException::class);
        $this->expectExceptionMessage("ERROR:  syntax error at or near \"XXX\"\nLINE 1: XXX\n        ^");

        $this->conn->query('XXX');
    }

    public function testPing(): void
    {
        $result = $this->conn->ping();
        $this->assertTrue($result);
        // @TODO how to test false?
    }

    public function testTransactions(): void
    {
        $this->assertEquals(PGSQL_TRANSACTION_IDLE, $this->conn->transactionStatus());

        $result = $this->conn->begin();
        $this->assertInstanceOf(PgSqlStatement::class, $result);
        $this->assertEquals(0, $result->numRows());
        $this->assertEquals(PGSQL_TRANSACTION_INTRANS, $this->conn->transactionStatus());

        $result = $this->conn->rollback();
        $this->assertInstanceOf(PgSqlStatement::class, $result);
        $this->assertEquals(0, $result->numRows());
        $this->assertEquals(PGSQL_TRANSACTION_IDLE, $this->conn->transactionStatus());

        $result = $this->conn->commit();
        $this->assertInstanceOf(PgSqlStatement::class, $result);
        $this->assertEquals(0, $result->numRows());
        $this->assertEquals(PGSQL_TRANSACTION_IDLE, $this->conn->transactionStatus());

        $result = $this->conn->rollback();
        $this->assertInstanceOf(PgSqlStatement::class, $result);
        $this->assertEquals(0, $result->numRows());
        $this->assertEquals(PGSQL_TRANSACTION_IDLE, $this->conn->transactionStatus());

        $result = $this->conn->begin();
        $this->assertInstanceOf(PgSqlStatement::class, $result);
        $this->assertEquals(0, $result->numRows());
        $this->assertEquals(PGSQL_TRANSACTION_INTRANS, $this->conn->transactionStatus());

        $result = $this->conn->begin();
        $this->assertInstanceOf(PgSqlStatement::class, $result);
        $this->assertEquals(0, $result->numRows());
        $this->assertEquals(PGSQL_TRANSACTION_INTRANS, $this->conn->transactionStatus());
    }

    public function testNotify(): void
    {
        $this->markAsRisky(); // This test sometimes fail

        $channel = md5((string)rand());
        $timeout = 10000;

        $conn = new PgSqlConnection(self::DSN, PGSQL_CONNECT_FORCE_NEW);
        $this->conn->query(sprintf('LISTEN "%s"', $channel));

        $start = microtime(true);
        $result = $this->conn->getNotify($timeout);
        $this->assertEquals([], $result);
        $end = microtime(true);

        $this->assertTrue($end - $start >= $timeout / 1000000, 'getNotify did not waited for timeout');

        $result = $conn->query('SELECT pg_backend_pid()');
        $pid = $result->fetchColumn();

        $conn->query(sprintf('NOTIFY "%s"', $channel));
        $result = $this->conn->getNotify();
        $this->assertEquals(['message' => $channel, 'pid' => $pid, 'payload' => ''], $result);

        $conn->query(sprintf('NOTIFY "%s", \'xxx\'', $channel));
        $result = $this->conn->getNotify();
        $this->assertEquals(['message' => $channel, 'pid' => $pid, 'payload' => 'xxx'], $result);
    }

    /**
     * @dataProvider escapeStringDataProvider
     * @param string $string
     * @param string $expected
     */
    public function testEscapeString(string $string, string $expected): void
    {
        $result = $this->conn->escapeString($string);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider escapeLiteralDataProvider
     * @param string $string
     * @param string $expected
     */
    public function testEscapeLiteral(string $string, string $expected): void
    {
        $result = $this->conn->escapeLiteral($string);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider escapeIdentifierDataProvider
     * @param string $string
     * @param string $expected
     */
    public function testEscapeIdentifier(string $string, string $expected): void
    {
        $result = $this->conn->escapeLiteral($string);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return string[][]
     */
    public function escapeStringDataProvider(): array
    {
        return [
            ['aa', 'aa'],
            ['100', '100'],
            ['aaa\'aa', "aaa''aa"],
        ];
    }

    /**
     * @return string[][]
     */
    public function escapeLiteralDataProvider(): array
    {
        return [
            ['aa', "'aa'"],
            ['100', "'100'"],
            ['aaa\'aa', "'aaa''aa'"],
            ['FALSE', "'FALSE'"],
        ];
    }

    /**
     * @return string[][]
     */
    public function escapeIdentifierDataProvider(): array
    {
        return [
            ['aa', "'aa'"],
            ['100', "'100'"],
            ['aaa\'aa', "'aaa''aa'"],
            ['aaa"aa', "'aaa\"aa'"],
            ['FALSE', "'FALSE'"],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->conn = new PgSqlConnection(self::DSN, PGSQL_CONNECT_FORCE_NEW);
    }
}
