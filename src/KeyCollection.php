<?php declare(strict_types=1);

namespace Starburst\EncryptedConfigLoader;

use ParagonIE\Halite\Alerts\InvalidMessage;
use ParagonIE\Halite\Symmetric\Crypto as SymmetricCrypto;
use ParagonIE\Halite\Symmetric\EncryptionKey;

final class KeyCollection implements KeyLoader
{
	/** @var array<mixed> */
	private array $currentMetaData = [];
	private EncryptionKey $activeKey;
	/** @var list<EncryptionKey|KeyLoader> */
	private array $keys;

	public function __construct(
		KeyLoader|EncryptionKey ...$keys,
	) {
		$this->keys = array_values($keys);
	}

	/**
	 * @param array<mixed> $metaData
	 */
	public function setCryptoMetaData(array $metaData): void
	{
		$this->currentMetaData = $metaData;
	}

	public function loadKey(): EncryptionKey
	{
		if (isset($this->activeKey)) {
			return $this->activeKey;
		}
		$testValue = $this->currentMetaData[Crypto::CRYPTO_KEY_VERSION_FIELD] ?? null;
		if (!is_string($testValue)) {
			/** @var EncryptionKey|KeyLoader $key */
			$key = current($this->keys);
			if ($key instanceof KeyLoader) {
				$key = $key->loadKey();
			}
			return $this->activeKey = $key;
		}

		foreach ($this->keys as $key) {
			if ($key instanceof KeyLoader) {
				$key = $key->loadKey();
			}

			try {
				SymmetricCrypto::decrypt($testValue, $key);
				return $this->activeKey = $key;
			}
			catch (InvalidMessage) {}
		}

		throw new \RuntimeException('Failed to locate valid encryption key');
	}
}
