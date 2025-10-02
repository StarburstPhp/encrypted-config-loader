<?php declare(strict_types=1);

namespace Starburst\EncryptedConfigLoader;

use ParagonIE\Halite\Symmetric\Crypto as SymmetricCrypto;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use Starburst\EncryptedConfigLoader\Exception\EncryptionException;
use Starburst\EncryptedConfigLoader\Values\DecryptedValue;
use Starburst\EncryptedConfigLoader\Values\EncryptedValue;

final class DefaultCrypto implements Crypto
{
	private EncryptionKey $loadedKey;

	public function __construct(
		private EncryptionKey|KeyLoader $key,
	) {}

	public function __clone(): void
	{
		$this->key = clone $this->key;
	}

	public function setCryptoMetaData(array $metaData): void
	{
		if ($this->key instanceof KeyLoader) {
			$this->key->setCryptoMetaData($metaData);
		}
		unset($this->loadedKey);
	}

	public function withKey(EncryptionKey|KeyLoader $key): Crypto
	{
		return new self($key);
	}

	public function decrypt(EncryptedValue|string $cipherText): DecryptedValue
	{
		if ($cipherText instanceof EncryptedValue) {
			$cipherText = $cipherText->toString();
		}
		try {
			return new DecryptedValue(SymmetricCrypto::decrypt($cipherText, $this->resolveKey())->getString());
		}
		catch (\Throwable $e) {
			throw new EncryptionException(
				'Failed to decrypt value',
				previous: $e,
			);
		}
	}

	public function encrypt(DecryptedValue $value): EncryptedValue
	{
		try {
			return new EncryptedValue(SymmetricCrypto::encrypt($value->hidden(), $this->resolveKey()));
		}
		catch (\Throwable $e) {
			throw new EncryptionException(
				'Failed to encrypt value',
				previous: $e,
			);
		}
	}

	private function resolveKey(): EncryptionKey
	{
		if (isset($this->loadedKey)) {
			return $this->loadedKey;
		}
		if ($this->key instanceof EncryptionKey) {
			$this->loadedKey = $this->key;
		}
		else {
			$this->loadedKey = $this->key->loadKey();
		}

		return $this->loadedKey;
	}
}
