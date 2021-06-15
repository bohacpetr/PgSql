<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

use PHPUnit\Framework\TestCase;
use stdClass;

class JsonConvertorTest extends TestCase
{

	/** @var JsonConvertor */
	private $convertor;

	/**
	 * @dataProvider fromStringDateProvider
	 * @param string|null $input
	 * @param mixed $expected
	 */
	public function testFromString(?string $input, $expected): void
	{
		$result = $this->convertor->fromString($input);
		$this->assertEquals($expected, $result);
	}

	/**
	 * This is only asymmetry in encode/decode. JSON null value cannot be encoded back.
	 */
	public function testNullFromString(): void
	{
		$result = $this->convertor->fromString('null');
		$this->assertNull($result);
	}

	/**
	 * @dataProvider fromStringDateProvider
	 * @param mixed $input
	 * @param string|null $expected
	 */
	public function testToString(?string $expected, $input): void
	{
		$result = $this->convertor->toString($input);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return mixed[][]
	 */
	public function fromStringDateProvider(): array
	{
		$object = new stdClass();
		$object->a = 'a';
		$object->b = 123;

		return [
			['1', 1],
			['"a"', 'a'],
			['true', true],
			['false', false],
			[null, null],
			['[1,2,3]', [1, 2, 3]],
			['{"a":"a","b":123}', $object],
		];
	}

	protected function setUp(): void
	{
		parent::setUp();

		$this->convertor = new JsonConvertor();
	}
}
