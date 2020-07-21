<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

use bohyn\PgSql\DataType\Point;
use PHPUnit\Framework\TestCase;

class PointConvertorTest extends TestCase
{

    /** @var PointConvertor */
    private $convertor;

    /**
     * @dataProvider dataProvider
     * @param string|null $input
     * @param Point|null $expected
     */
    public function testFromString(?string $input, ?Point $expected): void
    {
        $result = $this->convertor->fromString($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider dataProvider
     * @param string|null $expected
     * @param Point|null $input
     */
    public function testToString(?string $expected, ?Point $input): void
    {
        $result = $this->convertor->toString($input);
        $this->assertEquals($expected, $result);
    }

    public function testInvalidToString()
    {
        $this->expectException(TypeConversionException::class);
        $this->expectExceptionMessage('Value must be of type "bohyn\PgSql\DataType\Point" but "string" given');

        $this->convertor->toString('');
    }

    /**
     * @return string[][]|Point[][]
     */
    public function dataProvider(): array
    {
        return [
            ['(0,0)', new Point(0, 0)],
            ['(-1,-1)', new Point(-1, -1)],
            ['(1.1,1.1)', new Point(1.1, 1.1)],
            [null, null],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->convertor = new PointConvertor();
    }
}
