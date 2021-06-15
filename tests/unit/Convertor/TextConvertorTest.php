<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

use PHPStan\Testing\TestCase;

class TextConvertorTest extends TestCase
{

	/** @var TextConvertor */
	private $convertor;

	/**
	 * @dataProvider fromStringDateProvider
	 * @param string|null $input
	 * @param string|null $expected
	 */
	public function testFromString(?string $input, ?string $expected): void
	{
		$result = $this->convertor->fromString($input);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @dataProvider toStringDataProvider
	 * @param mixed $input
	 * @param string|null $expected
	 */
	public function testToString($input, ?string $expected): void
	{
		$result = $this->convertor->toString($input);
		$this->assertEquals($result, $expected);
	}

	/**
	 * @return mixed[][]
	 */
	public function fromStringDateProvider(): array
	{
		return [
			['aaa', 'aaa'],
			['', ''],
			[null, null],
		];
	}

	/**
	 * @return mixed[][]
	 */
	public function toStringDataProvider(): array
	{
		return [
			['aaa', 'aaa'],
			['123', '123'],
			[123, '123'],
			[null, null],
		];
	}

	protected function setUp(): void
	{
		parent::setUp();

		$this->convertor = new TextConvertor();
	}
}
