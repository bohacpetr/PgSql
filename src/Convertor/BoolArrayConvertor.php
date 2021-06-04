<?php

declare(strict_types=1);

namespace bohyn\PgSql\Convertor;

class BoolArrayConvertor implements ITypeConvertor
{

	private BoolConvertor $boolConvertor;

	public function __construct()
	{
		$this->boolConvertor = new BoolConvertor();
	}

	/**
	 * @param string|null $stringValue
	 * @return bool[]|null
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
	 */
	public function fromString(?string $stringValue): ?array
	{
		if ($stringValue === null) {
			return null;
		}

		$values = explode(',', trim($stringValue, '{}'));

		return array_map(
			function ($value): ?bool {
				if (strtoupper($value) === 'NULL') {
					return null;
				}

				return $this->boolConvertor->fromString($value);
			},
			$values
		);
	}

	/**
	 * @param bool[]|null[]|null $values
	 * @return string|null
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
	 */
	public function toString(mixed $values): ?string
	{
		if ($values === null) {
			return null;
		}

		$values = array_map(
			function ($value): string {
				if ($value === null) {
					return 'NULL';
				}

				/** @var string $value */
				$value = $this->boolConvertor->toString($value);

				return $value;
			},
			$values
		);

		return '{' . implode(',', $values) . '}';
	}
}
