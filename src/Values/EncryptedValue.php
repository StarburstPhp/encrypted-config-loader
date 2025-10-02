<?php declare(strict_types=1);

namespace Starburst\EncryptedConfigLoader\Values;

final class EncryptedValue
{
	public function __construct(
		#[\SensitiveParameter]
		private readonly string $value,
	) {}

	/**
	 * @return array<mixed>
	 */
	public function __debugInfo(): array
	{
		return [
			'value' => '***',
		];
	}

	public function toString(): string
	{
		return $this->value;
	}
}
