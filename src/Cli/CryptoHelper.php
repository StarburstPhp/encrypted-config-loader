<?php declare(strict_types=1);

namespace Starburst\EncryptedConfigLoader\Cli;

use ParagonIE\Halite\Symmetric\EncryptionKey;
use Starburst\EncryptedConfigLoader\KeyLoader;

interface CryptoHelper
{
	/**
	 * @param array<mixed> $config
	 * @return array<mixed>
	 */
	public function decryptConfig(array $config): array;

	/**
	 * @param array<mixed> $config
	 * @return array<mixed>
	 */
	public function encryptConfig(array $config): array;

	/**
	 * @param array<mixed> $config
	 * @param list<string> $header
	 */
	public function renderConfig(array $config, array $header): string;

	/**
	 * @return list<string>
	 */
	public function getDefaultDecryptedFileHeader(string $configFile): array;

	/**
	 * @return list<string>
	 */
	public function getDefaultEncryptedFileHeader(string $configFile): array;

	public function setKey(EncryptionKey|KeyLoader $key): void;
}
