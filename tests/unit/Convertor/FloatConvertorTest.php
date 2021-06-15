<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

use PHPUnit\Framework\TestCase;

class FloatConvertorTest extends TestCase
{

	/** @var FloatConvertor */
	private $convertor;

	/**
	 * @dataProvider fromStringDataProvider
	 * @param string|null $input
	 * @param float|null $expected
	 */
	public function testFromString(?string $input, ?float $expected): void
	{
		$result = $this->convertor->fromString($input);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @dataProvider toStringDataProvider
	 * @param string|null $expected
	 * @param float|null $input
	 */
	public function testToString(?float $input, ?string $expected): void
	{
		$result = $this->convertor->toString($input);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return int[]|null[]
	 */
	public function fromStringDataProvider(): array
	{
		return [
			['1', 1.0],
			['1', 1],
			['1.1', 1.1],
			['0', 0.0],
			['0.0', 0],
			['-0.1', -0.1],
			['-1', -1],
			[null, null],
		];
	}

	/**
	 * @return int[]|null[]
	 */
	public function toStringDataProvider(): array
	{
		return [
			[1.0, '1'],
			[1, '1'],
			[1.1, '1.1'],
			[0.0, '0'],
			[0, '0'],
			[-0.1, '-0.1'],
			[-1, '-1'],
			[null, null],
		];
	}

	protected function setUp(): void
	{
		parent::setUp();

		$this->convertor = new FloatConvertor();
	}
}
