<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;

class DateConvertorTest extends TestCase
{

    /** @var DateConvertor */
    private $convertor;

    /**
     * @dataProvider dataProvider
     * @param string|null $input
     * @param DateTimeInterface|null $expected
     */
    public function testFromString(?string $input, ?DateTimeInterface $expected): void
    {
        $result = $this->convertor->fromString($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider dataProvider
     * @param string|null $expected
     * @param DateTimeInterface|null $input
     */
    public function testToString(?string $expected, ?DateTimeInterface $input): void
    {
        $result = $this->convertor->toString($input);
        $this->assertEquals($expected, $result);
    }

    public function testInvalidToString(): void
    {
        $this->expectException(TypeConversionException::class);
        $this->expectExceptionMessage('Invalid date value "aaa"');

        $this->convertor->toString('aaa');
    }

    public function testInvalidFromString(): void
    {
        $this->expectException(TypeConversionException::class);
        $this->expectExceptionMessage('Invalid date value "aaa"');

        $this->convertor->fromString('aaa');
    }

    /**
     * @return string[][]|DateTimeInterface[][]
     */
    public function dataProvider(): array
    {
        return [
            ['2020-07-15', new DateTimeImmutable('2020-07-15 00:00:00.0')],
            ['2020-07-15', new DateTime('2020-07-15 00:00:00.0')],
            [null, null],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->convertor = new DateConvertor();
    }
}
