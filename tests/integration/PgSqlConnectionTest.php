<?php

declare(strict_types=1);

namespace bohyn\PgSql;

use Closure;
use PHPUnit\Framework\TestCase;

class PgSqlConnectionTest extends TestCase
{

    private const DSN = 'host=localhost user=postgres password=postgres';

    /** @var PgSqlConnection */
    private $conn;

    public function testConnect(): void
    {
        new PgSqlConnection(self::DSN, PGSQL_CONNECT_FORCE_NEW);

        $this->expectException(PgSqlException::class);
        $this->expectExceptionMessage("Connection error\nConnect\nNULL");

        new PgSqlConnection('host=localhost user=nonexisting password=nonexisting');
    }

    public function testPing(): void
    {
        $result = $this->conn->ping();
        $this->assertTrue($result);

        (Closure::bind(
            function (): void {
                @pg_query('SELECT pg_terminate_backend(pg_backend_pid())');
            },
            $this->conn,
            $this->conn
        ))();

        $result = $this->conn->ping();
        $this->assertFalse($result);
    }

    public function testNotify(): void
    {
        $channel = md5((string)rand());
        $timeout = 10000;

        $this->conn->query(sprintf('LISTEN "%s"', $channel));

        $start = microtime(true);
        $result = $this->conn->getNotify($timeout);
        $this->assertEquals([], $result);
        $end = microtime(true);

        $this->assertTrue($end - $start >= $timeout / 1000000, 'getNotify did not waited 1s for timeout');

        $this->conn->query(sprintf('NOTIFY "%s"', $channel));
        $result = $this->conn->getNotify();
        unset($result['pid']);
        $this->assertEquals(['message' => $channel, 'payload' => ''], $result);

        $this->conn->query(sprintf('NOTIFY "%s", \'xxx\'', $channel));
        $result = $this->conn->getNotify();
        unset($result['pid']);
        $this->assertEquals(['message' => $channel, 'payload' => 'xxx'], $result);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->conn = new PgSqlConnection(self::DSN);
    }
}
