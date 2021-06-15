<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

use PHPUnit\Framework\TestCase;

class IntegerArrayConvertorTest extends TestCase
{

	/** @var IntegerArrayConvertor */
	private $convertor;

	/**
	 * @dataProvider dataProvider
	 * @param string|null $input
	 * @param mixed[]|null $expected
	 */
	public function testFromString(?string $input, ?array $expected): void
	{
		$result = $this->convertor->fromString($input);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @dataProvider dataProvider
	 * @param string|null $expected
	 * @param mixed[]|null $input
	 */
	public function testToString(?string $expected, ?array $input): void
	{
		$result = $this->convertor->toString($input);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return mixed[][]
	 */
	public function dataProvider(): array
	{
		return [
			['{1,2,3}', [1, 2, 3]],
			['{1,NULL,3}', [1, null, 3]],
			['{-1,2,-3}', [-1, 2, -3]],
			[null, null],
		];
	}

	protected function setUp(): void
	{
		parent::setUp();

		$this->convertor = new IntegerArrayConvertor();
	}
}
