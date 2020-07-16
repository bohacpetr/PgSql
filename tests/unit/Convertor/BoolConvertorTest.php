<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

use PHPUnit\Framework\TestCase;

class BoolConvertorTest extends TestCase
{

    /** @var BoolConvertor */
    private $convertor;

    /**
     * @dataProvider toStringDataProvider
     * @param mixed $input
     * @param string|null $expected
     */
    public function testToString($input, ?string $expected): void
    {
        $result = $this->convertor->toString($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider toStringInvalidDataProvider
     * @param mixed $input
     */
    public function testInvalidDataToString($input): void
    {
        $this->expectException(TypeConversionException::class);
        $this->expectExceptionMessage('Unexpected value, expecting boolean');

        $this->convertor->toString($input);
    }

    /**
     * @dataProvider fromStringDataProvider
     * @param string|null $input
     * @param bool|null $expected
     */
    public function testFromString(?string $input, ?bool $expected): void
    {
        $result = $this->convertor->fromString($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider fromStringInvalidDataProvider
     * @param mixed $input
     */
    public function testInvalidDataFromString($input): void
    {
        $this->expectException(TypeConversionException::class);
        $this->expectExceptionMessage('Unexpected value, expecting "t" or "f"');

        $this->convertor->fromString($input);
    }

    /**
     * @return mixed[][]
     */
    public function toStringDataProvider(): array
    {
        return [
            [true, 't'],
            [false, 'f'],
            [null, null],
        ];
    }

    /**
     * @return mixed[][]
     */
    public function toStringInvalidDataProvider(): array
    {
        return [
            ['NULL'],
            ['aa'],
        ];
    }

    /**
     * @return mixed[][]
     */
    public function fromStringDataProvider(): array
    {
        return [
            ['t', true],
            ['f', false],
            [null, null],
        ];
    }

    /**
     * @return mixed[][]
     */
    public function fromStringInvalidDataProvider(): array
    {
        return [
            ['true'],
            ['false'],
            ['0'],
            ['1'],
            [''],
            ['aa'],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->convertor = new BoolConvertor();
    }
}
