<?php declare(strict_types=1);

namespace Starburst\EncryptedConfigLoader;

use Starburst\EncryptedConfigLoader\Values\DecryptedValue;
use Starburst\EncryptedConfigLoader\Values\EncryptedValue;
use Stefna\Config\Config;
use Stefna\Config\GetConfigTrait;

final class EncryptedConfig implements Config
{
	use GetConfigTrait;

	/** @var array<string, DecryptedValue> */
	private array $decryptedValues = [];
	private readonly Crypto $crypto;

	public function __construct(
		/** @var array<string, mixed> */
		private readonly array $config,
		Crypto $crypto,
	) {
		if (
			isset($this->config[Crypto::CRYPTO_META_FIELD])
			&& is_array($this->config[Crypto::CRYPTO_META_FIELD])
		) {
			$crypto = clone $crypto;
			$crypto->setCryptoMetaData($this->config[Crypto::CRYPTO_META_FIELD]);
		}
		$this->crypto = $crypto;
	}

	public function getRawValue(string $key): mixed
	{
		if (isset($this->config[$key])) {
			if (is_array($this->config[$key])) {
				return $this->resolveArray($this->config[$key]);
			}
			return $this->resolveValue($this->config[$key], $key);
		}
		if (!str_contains($key, '.')) {
			return null;
		}
		$keys = explode('.', $key);
		$root = $this->config;
		foreach ($keys as $searchKey) {
			if (!is_array($root) || !isset($root[$searchKey])) {
				return null;
			}
			$root = $root[$searchKey];
		}
		if (is_array($root)) {
			return $this->resolveArray($root);
		}
		return $this->resolveValue($root, $key);
	}

	private function resolveValue(mixed $value, string $key): mixed
	{
		if ($value instanceof EncryptedValue) {
			if (!isset($this->decryptedValues[$key])) {
				$this->decryptedValues[$key] = $this->crypto->decrypt($value);
			}

			return $this->decryptedValues[$key]->toString();
		}
		elseif ($value instanceof DecryptedValue) {
			return $value->toString();
		}
		return $value;
	}

	public function getArray(string $key, array $default = []): array
	{
		$value = $this->getRawValue($key) ?? $default;
		if (!is_array($value)) {
			return $default;
		}

		return $this->resolveArray($value);
	}

	/**
	 * @param array<mixed> $values
	 * @return array<mixed>
	 */
	private function resolveArray(array $values): array
	{
		foreach ($values as $k => $v) {
			if (is_array($v)) {
				$values[$k] = $this->resolveArray($v);
			}
			else {
				$values[$k] = $this->resolveValue($v, $k);
			}
		}
		return $values;
	}
}
