<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

use PHPUnit\Framework\TestCase;

class IntegerConvertorTest extends TestCase
{

	/** @var IntegerConvertor */
	private $convertor;

	/**
	 * @dataProvider dataProvider
	 * @param string|null $input
	 * @param int|null $expected
	 */
	public function testFromString(?string $input, ?int $expected): void
	{
		$result = $this->convertor->fromString($input);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @dataProvider dataProvider
	 * @param int|null $expected
	 * @param string|null $input
	 */
	public function testToString(?int $expected, ?string $input): void
	{
		$result = $this->convertor->toString($input);
		$this->assertEquals($expected, $result);
	}

	public function testInvalidFromString(): void
	{
		$this->expectException(TypeConversionException::class);
		$this->expectExceptionMessage('Non-numeric input');

		$this->convertor->fromString('aaa');
	}

	/**
	 * @dataProvider invalidToStringDataProvider
	 * @param mixed $input
	 */
	public function testInvalidDataToString($input): void
	{
		$this->expectException(TypeConversionException::class);
		$this->expectExceptionMessage('Non-numeric input');

		$this->convertor->toString($input);
	}

	/**
	 * @return int[]|null[]
	 */
	public function dataProvider(): array
	{
		return [
			['1', 1],
			['0', 0],
			['-1', -1],
			[null, null],
		];
	}

	/**
	 * @return mixed[]
	 */
	public function invalidToStringDataProvider(): array
	{
		return [
			['a'],
			[true],
			[false],
			[[]],
		];
	}

	protected function setUp(): void
	{
		parent::setUp();

		$this->convertor = new IntegerConvertor();
	}
}
