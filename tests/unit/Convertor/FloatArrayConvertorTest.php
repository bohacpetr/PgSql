<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

use PHPUnit\Framework\TestCase;

class FloatArrayConvertorTest extends TestCase
{

    /** @var IntegerArrayConvertor */
    private $convertor;

    /**
     * @dataProvider dataProvider
     * @param string|null $input
     * @param int[]|null[]|null $expected
     */
    public function testFromString(?string $input, ?array $expected): void
    {
        $result = $this->convertor->fromString($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider dataProvider
     * @param string|null $expected
     * @param int[]|null[]|null $input
     */
    public function testToString(?string $expected, ?array $input): void
    {
        $result = $this->convertor->toString($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return string[][]|int[][][]
     */
    public function dataProvider(): array
    {
        return [
            ['{1,2.1,3}', [1, 2.1, 3]],
            ['{1,NULL,3.9}', [1, null, 3.9]],
            ['{-1.1,2.2,-3.3}', [-1.1, 2.2, -3.3]],
            ['{NULL,NULL,NULL}', [null, null, null]],
            [null, null],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->convertor = new FloatArrayConvertor();
    }
}
