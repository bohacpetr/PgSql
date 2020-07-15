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

    /**
     * @return string[][]|DateTimeInterface[][]
     */
    public function dataProvider(): array
    {
        return [
            ['2020-07-15', new DateTimeImmutable('2020-07-15 00:00:00')],
            ['2020-07-15', new DateTime('2020-07-15 00:00:00')],
            [null, null],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->convertor = new DateConvertor();
    }
}
