<?php declare(strict_types=1);

namespace Starburst\EncryptedConfigLoader\Tests\Cli;

use ParagonIE\Halite\Symmetric\EncryptionKey;
use PHPUnit\Framework\TestCase;
use Starburst\EncryptedConfigLoader\Cli\CryptoHelper;
use Starburst\EncryptedConfigLoader\Cli\DefaultCryptoHelper;
use Starburst\EncryptedConfigLoader\Crypto;
use Starburst\EncryptedConfigLoader\DefaultCrypto;
use Starburst\EncryptedConfigLoader\StringKeyResolver;
use Starburst\EncryptedConfigLoader\Values\DecryptedValue;
use Starburst\EncryptedConfigLoader\Values\EncryptedValue;

final class CryptoHelperTest extends TestCase
{
	private const KEY_2 = '31400500acc287e54074c1cff2adf5d3e852824885bc0b6b998f8a1427e70ddbb28e34b7782d8384da9fb4499cb8b4093f141456fd56f0e847197a83ff164653fdc38944b6584373233a5075e9e5d52b99343338eaec451f756055eda6154ddceac91e7d';

	private function loadKey(string $key): EncryptionKey
	{
		return (new StringKeyResolver())->resolveKey($key);
	}

	private function getCrypto(string $key): Crypto
	{
		return new DefaultCrypto($this->loadKey($key));
	}

	private function getCryptoHelper(string $key): CryptoHelper
	{
		return new DefaultCryptoHelper($this->getCrypto($key));
	}

	public function testDecryptConfig(): void
	{
		$rawConfig2 = [
			'override' => 42,
			'decryptedValue' => new DecryptedValue('test'),
			'secret' => new EncryptedValue('MUIFABIrEuQeF97ULjnCoFZB2mcpgDOmc-mV2IHtR87TiH5oZywGIBXyi6epyN1DSIqaURhhsMfXVxzS4O_qLTYDHGkk2ehTfG4B9bTDR_TtszWxy6hh-ExmdNOxWKy_IEydevhm3cK4eAoj1IPBKejam8-pZaj-iSuF6DiPPsh_dtzZhTuMLg=='),
			'nested' => [
				'random' => [
					'secret1' => new EncryptedValue('MUIFAMCAHYllJ7gVcCGyuv8Qom7azz7toGGpFlnMQix4d5iPTSxGnAoVWA5r0nFhxIiYv2ZKgdWRG4AEkwg8dRVGOjln2ATn0c1gfJW33MX21j6Z7x4VK2dBbc3k6MlccveOa5weiHF4oYw8AygmAOxx07yVHQFSpjOjoLqVQT9JiNqninRv3A=='),
				],
			],
			'__CRYPTO_META' => [
				'tenant' => '3019f12a-cb55-4362-8cd5-b7efb7aec3f2',
				'__KEY_VERSION' => 'MUIFABhtMPMgSqmy_obWV0lmfPdzCA5PTrRurvlQED9vPa4sc7AIqBHlmHNg2RVOZG7Uc9uwLuEuMSZvBFPUoksTg33qZXObcTd8EOZoW-_-4LzPwg6NPjTIkUEEVTtzbIOL4CsI_tg3dIhI1HKgi2jUE5mc_REyA3SEnAg=',
			],
		];

		$helper = $this->getCryptoHelper(self::KEY_2);
		$decryptedConfig = $helper->decryptConfig($rawConfig2);

		$this->assertInstanceOf(DecryptedValue::class, $decryptedConfig['decryptedValue']);
		$this->assertInstanceOf(DecryptedValue::class, $decryptedConfig['secret']);
		$this->assertInstanceOf(DecryptedValue::class, $decryptedConfig['nested']['random']['secret1']);
	}

	public function testEncryptConfig(): void
	{
		$rawConfig2 = [
			'override' => 42,
			'decryptedValue' => new DecryptedValue('test'),
			'secret' => new DecryptedValue('values'),
			'nested' => [
				'random' => [
					'secret1' => new DecryptedValue('random'),
				],
			],
		];

		$helper = $this->getCryptoHelper(self::KEY_2);
		$decryptedConfig = $helper->encryptConfig($rawConfig2);

		$this->assertInstanceOf(EncryptedValue::class, $decryptedConfig['decryptedValue']);
		$this->assertInstanceOf(EncryptedValue::class, $decryptedConfig['secret']);
		$this->assertInstanceOf(EncryptedValue::class, $decryptedConfig['nested']['random']['secret1']);

		$this->assertArrayHasKey(Crypto::CRYPTO_META_FIELD, $decryptedConfig);
	}
}
