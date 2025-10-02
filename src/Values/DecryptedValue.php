<?php declare(strict_types=1);

namespace Starburst\EncryptedConfigLoader\Values;

use ParagonIE\HiddenString\HiddenString;

final class DecryptedValue
{
	public function __construct(
		#[\SensitiveParameter]
		public readonly string $value,
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

	public function hidden(): HiddenString
	{
		return new HiddenString($this->value);
	}
}
