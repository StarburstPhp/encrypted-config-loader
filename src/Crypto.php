<?php declare(strict_types=1);

namespace Starburst\EncryptedConfigLoader;

use ParagonIE\Halite\Symmetric\EncryptionKey;
use Starburst\EncryptedConfigLoader\Values\DecryptedValue;
use Starburst\EncryptedConfigLoader\Values\EncryptedValue;

interface Crypto
{
	public const CRYPTO_KEY_VERSION_FIELD = '__KEY_VERSION';
	public const CRYPTO_META_FIELD = '__CRYPTO_META';

	/**
	 * @param array<mixed> $metaData
	 */
	public function setCryptoMetaData(array $metaData): void;

	public function withKey(EncryptionKey|KeyLoader $key): Crypto;

	public function decrypt(EncryptedValue|string $cipherText): DecryptedValue;

	public function encrypt(DecryptedValue $value): EncryptedValue;
}
