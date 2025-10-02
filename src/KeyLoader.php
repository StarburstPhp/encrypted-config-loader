<?php declare(strict_types=1);

namespace Starburst\EncryptedConfigLoader;

use ParagonIE\Halite\Symmetric\EncryptionKey;

interface KeyLoader
{
	/**
	 * @param array<mixed> $metaData
	 */
	public function setCryptoMetaData(array $metaData): void;

	public function loadKey(): EncryptionKey;
}
