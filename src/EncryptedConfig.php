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
}
