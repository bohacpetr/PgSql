<?php

declare(strict_types=1);

namespace bohyn\PgSql;

use bohyn\PgSql\Convertor\ConvertorCollection;
use PHPUnit\Framework\TestCase;

class PgSqlBuilderTest extends TestCase
{

    /** @var PgSqlBuilder */
    private $builder;

    /**
     * @dataProvider buildSelectDataProvider
     * @param string $table
     * @param string $expectedSelect
     */
    public function testBuildSelect(string $table, string $expectedSelect): void
    {
        $select = $this->builder->buildSelect($table);
        $this->assertEquals($expectedSelect, $select);
    }

    /**
     * @dataProvider buildUpdateDataProvider
     * @param string $table
     * @param mixed[] $values
     * @param mixed[] $predicates
     * @param string $expectedUpdate
     * @param mixed[] $expectedParameters
     */
    public function testBuildUpdate(
        string $table,
        array $values,
        array $predicates,
        string $expectedUpdate,
        array $expectedParameters
    ): void {
        $update = $this->builder->buildUpdate($table, $values, $predicates);
        $this->assertEquals($expectedUpdate, $update);
        $this->assertEquals($expectedParameters, $predicates);
    }

    /**
     * @dataProvider buildInsertDataProvider
     * @param string $table
     * @param mixed[] $values
     * @param string $expected
     */
    public function testBuildInsert(string $table, array $values, string $expected): void
    {
        $sqlInsert = $this->builder->buildInsert($table, $values);
        $this->assertEquals($expected, $sqlInsert);
    }

    /**
     * @dataProvider buildDeleteDataProvider
     * @param string $table
     * @param mixed[] $predicates
     * @param string $expected
     */
    public function testBuildDelete(string $table, array $predicates, string $expected): void
    {
        $sqlDelete = $this->builder->buildDelete($table, $predicates);
        $this->assertEquals($expected, $sqlDelete);
    }

    /**
     * @dataProvider buildWhereDataProvider
     * @param mixed[] $predicates
     * @param string $expectedSqlFragment
     * @param mixed[] $expectedPredicates
     */
    public function testBuildWhere(array $predicates, string $expectedSqlFragment, array $expectedPredicates): void
    {
        $sqlFragment = $this->builder->buildWhere($predicates);
        $this->assertEquals($expectedSqlFragment, $sqlFragment);
        $this->assertEquals($expectedPredicates, $predicates);
    }

    /**
     * @return mixed[][]
     */
    public function buildSelectDataProvider(): array
    {
        return [
            [
                'test',
                'SELECT * FROM "test"',
            ],
            [
                'test.test',
                'SELECT * FROM "test"."test"',
            ],
        ];
    }

    /**
     * @return mixed[][]
     */
    public function buildWhereDataProvider(): array
    {
        return [
            [
                ['id' => 1, 'name' => 'aaa'],
                ' WHERE "id" = $1 AND "name" = $2',
                [1, 'aaa'],
            ],
            [
                ['id' => null, 'name' => 'aaa'],
                ' WHERE "id" IS NULL AND "name" = $1',
                ['aaa'],
            ],
            [
                ['id' => [1, 2, 3], 'name' => 'a\'aa'],
                ' WHERE "id" = ANY ($1) AND "name" = $2',
                [[1, 2, 3], 'a\'aa'],
            ],
        ];
    }

    /**
     * @return mixed[][]
     */
    public function buildUpdateDataProvider(): array
    {
        return [
            [
                'test',
                ['id' => 2, 'name' => 'bbb'],
                ['id' => 1, 'name' => 'aaa'],
                'UPDATE "test" SET "id" = $1, "name" = $2 WHERE "id" = $3 AND "name" = $4 RETURNING *',
                [2, 'bbb', 1, 'aaa'],
            ],
            [
                'test',
                ['id' => 2, 'name' => null],
                ['id' => 1, 'name' => null],
                'UPDATE "test" SET "id" = $1, "name" = $2 WHERE "id" = $3 AND "name" IS NULL RETURNING *',
                [2, null, 1],
            ],
            [
                'test',
                ['id' => 2, 'name' => ['a', 'b']],
                ['id' => 1, 'name' => 'aaa'],
                'UPDATE "test" SET "id" = $1, "name" = $2 WHERE "id" = $3 AND "name" = $4 RETURNING *',
                [2, ['a', 'b'], 1, 'aaa'],
            ],
        ];
    }

    /**
     * @return mixed[][]
     */
    public function buildInsertDataProvider(): array
    {
        return [
            ['test', ['id' => 1], 'INSERT INTO "test" ("id") VALUES ($1) RETURNING *'],
            ['test', ['id' => 1, 'name' => 'aa'], 'INSERT INTO "test" ("id", "name") VALUES ($1, $2) RETURNING *'],
        ];
    }

    /**
     * @return mixed[][]
     */
    public function buildDeleteDataProvider(): array
    {
        return [
            ['test', ['id' => 1], 'DELETE FROM "test" WHERE "id" = $1 RETURNING *'],
            ['test', ['id' => 1, 'name' => 'aa'], 'DELETE FROM "test" WHERE "id" = $1 AND "name" = $2 RETURNING *'],
        ];
    }

    public function setUp(): void
    {
        parent::setUp();

        $conn = new PgSqlConnection(
            'host=localhost user=postgres password=postgres',
            new ConvertorCollection(),
            PGSQL_CONNECT_FORCE_NEW
        );
        $this->builder = new PgSqlBuilder($conn);
    }
}
