<?php declare(strict_types=1);

namespace Starburst\EncryptedConfigLoader\Cli;

use ParagonIE\Halite\Symmetric\EncryptionKey;
use Starburst\EncryptedConfigLoader\Crypto;
use Starburst\EncryptedConfigLoader\KeyLoader;
use Starburst\EncryptedConfigLoader\Values\DecryptedValue;
use Starburst\EncryptedConfigLoader\Values\EncryptedValue;

final class DefaultCryptoHelper implements CryptoHelper
{
	private const CRYPTO_MAGIC_VALUE = '1';

	public function __construct(
		private Crypto $crypto,
	) {}

	/**
	 * @param array<mixed> $config
	 * @return array<mixed>
	 */
	public function decryptConfig(array $config, bool $root = true): array
	{
		if ($root) {
			if (
				isset($config[Crypto::CRYPTO_META_FIELD])
				&& is_array($config[Crypto::CRYPTO_META_FIELD])
			) {
				unset($config[Crypto::CRYPTO_META_FIELD][Crypto::CRYPTO_KEY_VERSION_FIELD]);
			}
		}
		foreach ($config as &$value) {
			if ($value instanceof EncryptedValue) {
				$value = $this->crypto->decrypt($value->toString());
				continue;
			}
			if (is_array($value)) {
				$value = $this->decryptConfig($value, false);
			}
		}

		return $config;
	}

	/**
	 * @param array<mixed> $config
	 * @return array<mixed>
	 */
	public function encryptConfig(array $config, bool $root = true): array
	{
		if ($root) {
			$config[Crypto::CRYPTO_META_FIELD] ??= [];
			if (!is_array($config[Crypto::CRYPTO_META_FIELD])) {
				throw new \RuntimeException(sprintf(
					'Invalid format for meta data key. Expected "array", got "%s"',
					get_debug_type($config[Crypto::CRYPTO_META_FIELD]),
				));
			}
			$config[Crypto::CRYPTO_META_FIELD][Crypto::CRYPTO_KEY_VERSION_FIELD] = $this->crypto->encrypt(
				new DecryptedValue(self::CRYPTO_MAGIC_VALUE),
			)->toString();
		}
		foreach ($config as &$value) {
			if ($value instanceof DecryptedValue) {
				$value = $this->crypto->encrypt($value);
				continue;
			}
			if (is_array($value)) {
				$value = $this->encryptConfig($value, false);
			}
		}

		return $config;
	}

	/**
	 * @param array<mixed> $config
	 * @param list<string> $header
	 */
	public function renderConfig(array $config, array $header): string
	{
		$header[] = '// Generated on ' . date('Y-m-d H:i:s');

		$outputContent = "<?php declare(strict_types=1);\n\n";
		$outputContent .= implode("\n", $header) . "\n\nreturn ";
		$outputContent .= var_export($config, true);
		$outputContent .= ';' . PHP_EOL;

		$outputContent = (string)preg_replace("/([\w\\\]+)::__set_state\(array\(\n\s+'value' => (.*),\n\s+\)\)/", 'new $1($2)', $outputContent);

		return $outputContent;
	}

	/**
	 * @return list<string>
	 */
	public function getDefaultDecryptedFileHeader(string $configFile): array
	{
		return [
			'// This file SHOULD NOT be checked into version control',
			'//',
			'// This file is just meant to exists while updating config secrets',
			'// After your done editing this file run:',
			'//',
			'//     starburst config:encrypt ' . $configFile,
			'//',
			'// Original file: ' . $configFile,
		];
	}

	/**
	 * @return list<string>
	 */
	public function getDefaultEncryptedFileHeader(string $configFile): array
	{
		return [
			'// This file SHOULD be checked into version control',
			'//',
			'// This file can be edited to update config values that aren\'t secrets.',
			'// But this file can only contain simple values since it converted back and forth when encrypting/decrypting',
			'//',
			'// To edit secrets run:',
			'//',
			'//     starburst config:decrypt ' . $configFile,
			'//',
		];
	}

	public function setKey(EncryptionKey|KeyLoader $key): void
	{
		$this->crypto = $this->crypto->withKey($key);
	}
}
