<?php declare(strict_types=1);

namespace Starburst\EncryptedConfigLoader\Tests;

use ParagonIE\Halite\Symmetric\EncryptionKey;
use PHPUnit\Framework\TestCase;
use Starburst\EncryptedConfigLoader\Crypto;
use Starburst\EncryptedConfigLoader\DefaultCrypto;
use Starburst\EncryptedConfigLoader\EncryptedConfig;
use Starburst\EncryptedConfigLoader\KeyCollection;
use Starburst\EncryptedConfigLoader\StringKeyResolver;
use Starburst\EncryptedConfigLoader\Values\DecryptedValue;
use Starburst\EncryptedConfigLoader\Values\EncryptedValue;
use Stefna\Config\ChainConfig;

final class EncryptedConfigTest extends TestCase
{
	private const KEY_1 = '3140050047120b30d5bedacdc7ecf542a23ea6c609b2e8c61ab773206e43c8fef69b0b606aea2bc90247940aadc766bbe8a0fe0f9446b6cd29c14b9a729641b0c35c44e893a257bf6dd836c9b3904b69306d1063740fcd39fc5b12389c8c47d40984fba6';
	private const KEY_2 = '31400500acc287e54074c1cff2adf5d3e852824885bc0b6b998f8a1427e70ddbb28e34b7782d8384da9fb4499cb8b4093f141456fd56f0e847197a83ff164653fdc38944b6584373233a5075e9e5d52b99343338eaec451f756055eda6154ddceac91e7d';

	private function loadKey(string $key): EncryptionKey
	{
		return (new StringKeyResolver())->resolveKey($key);
	}

	private function getCrypto(): Crypto
	{
		return new DefaultCrypto($this->loadKey(self::KEY_1));
	}

	public function testDecryptionOnDemand(): void
	{
		$rawConfig = [
			'foo' => 'bar',
			'baz' => new EncryptedValue('MUIFAITl_sd3PAddvZl7IOo38wuD0YPhkjlP4D6ZCbtcdNTAcmUJPxRjfKxHOpnhWyvgTfVaJ5yuhVIpLGQKBlw1-bEPayiLxsdL0Vuj16VVPB0tLVTSvRzCzx-NB32C98Rolc-WXbjD2npC4IzZVRawuBMMOV4DUVOqBjBFAkPRK0XoVn6brw=='),
			'test' => [
				'foo' => new EncryptedValue('MUIFAMRjgba3xFdAMDYL0rrJYdhs0Jr-WTsEG6wOcPYZKtyiDj16REFaihQKqhifYU_nlFhkgjzTDEW5Bzv8izDpdqjP6pIN_HmkHbukJMlEJB6DZOB-kz30GR9Onj4yCU-xVOaI2gzK4sykEZ0E623BSKa1cygCDUmaMt1BlnnjXROZV4dmA0aM'),
			],
		];
		$config = new EncryptedConfig($rawConfig, $this->getCrypto());

		$this->assertSame('Secret value 2', $config->getString('test.foo'));
	}

	public function testHandlingOfDecryptedValueObjectInConfig(): void
	{
		$rawConfig = [
			'foo' => 'bar',
			'baz' => new EncryptedValue('MUIFAITl_sd3PAddvZl7IOo38wuD0YPhkjlP4D6ZCbtcdNTAcmUJPxRjfKxHOpnhWyvgTfVaJ5yuhVIpLGQKBlw1-bEPayiLxsdL0Vuj16VVPB0tLVTSvRzCzx-NB32C98Rolc-WXbjD2npC4IzZVRawuBMMOV4DUVOqBjBFAkPRK0XoVn6brw=='),
			'test' => [
				'foo' => new DecryptedValue('test 2'),
			],
		];
		$config = new EncryptedConfig($rawConfig, $this->getCrypto());

		$this->assertSame('test 2', $config->getString('test.foo'));
		$this->assertSame('Secret value', $config->getString('baz'));
	}

	public function testConfigWithKeyCollection(): void
	{
		$keyCollection = new KeyCollection($this->loadKey(self::KEY_1));
		$crypto = $this->getCrypto()->withKey($keyCollection);
		$rawConfig = [
			'foo' => 'bar',
			'baz' => new EncryptedValue('MUIFAITl_sd3PAddvZl7IOo38wuD0YPhkjlP4D6ZCbtcdNTAcmUJPxRjfKxHOpnhWyvgTfVaJ5yuhVIpLGQKBlw1-bEPayiLxsdL0Vuj16VVPB0tLVTSvRzCzx-NB32C98Rolc-WXbjD2npC4IzZVRawuBMMOV4DUVOqBjBFAkPRK0XoVn6brw=='),
			'test' => [
				'foo' => new DecryptedValue('test 2'),
			],
		];
		$config = new EncryptedConfig($rawConfig, $crypto);

		$this->assertSame('test 2', $config->getString('test.foo'));
		$this->assertSame('Secret value', $config->getString('baz'));
	}

	public function testChainConfigWithDifferentKeys(): void
	{
		$keyCollection = new KeyCollection(
			$this->loadKey(self::KEY_1),
			$this->loadKey(self::KEY_2),
		);
		$crypto = $this->getCrypto()->withKey($keyCollection);
		$rawConfig1 = [
			'nested' => [
				'random' => [
					'new-key' => new \Starburst\EncryptedConfigLoader\Values\EncryptedValue('MUIFAArDaF_wsQ9a435CvelFIkFFZJDPuOLEZpIO9lyLCV-k05wCYTAlJX1InTNXhXJ8g5Zl_ERAfx2Sd1__Y0-aL47n-Mx_RM89GGm9J3HzObYyB4XLpMEywYKY6V9VhugLEe0x2FpghQixRi2CZCfMMNPcsMLM_1ASiK5-rWfEouY4e3wBDMSa0-0='),
				],
			],
			'__CRYPTO_META' => [
				'__KEY_VERSION' => 'MUIFAM7jrDc2ELG7ynPtfs1lwwdCyOc5zQ75MMbFESgd_uyebH9QrhWT6xu4e1dYFL9q2ed9squOb2B4cv2xood_ZhSrj-COOqwVOyj6L_V_5FVxWHZjZo2W3aW1VajKtFzXZSsL7jZLEum1Fn3luOyPl-L9MwSD02d3_Ig=',
			],
		];
		$rawConfig2 = [
			'override' => true,
			'secret' => new \Starburst\EncryptedConfigLoader\Values\EncryptedValue('MUIFABIrEuQeF97ULjnCoFZB2mcpgDOmc-mV2IHtR87TiH5oZywGIBXyi6epyN1DSIqaURhhsMfXVxzS4O_qLTYDHGkk2ehTfG4B9bTDR_TtszWxy6hh-ExmdNOxWKy_IEydevhm3cK4eAoj1IPBKejam8-pZaj-iSuF6DiPPsh_dtzZhTuMLg=='),
			'nested' => [
				'random' => [
					'secret1' => new \Starburst\EncryptedConfigLoader\Values\EncryptedValue('MUIFAMCAHYllJ7gVcCGyuv8Qom7azz7toGGpFlnMQix4d5iPTSxGnAoVWA5r0nFhxIiYv2ZKgdWRG4AEkwg8dRVGOjln2ATn0c1gfJW33MX21j6Z7x4VK2dBbc3k6MlccveOa5weiHF4oYw8AygmAOxx07yVHQFSpjOjoLqVQT9JiNqninRv3A=='),
				],
			],
			'__CRYPTO_META' => [
				'tenant' => '3019f12a-cb55-4362-8cd5-b7efb7aec3f2',
				'__KEY_VERSION' => 'MUIFABhtMPMgSqmy_obWV0lmfPdzCA5PTrRurvlQED9vPa4sc7AIqBHlmHNg2RVOZG7Uc9uwLuEuMSZvBFPUoksTg33qZXObcTd8EOZoW-_-4LzPwg6NPjTIkUEEVTtzbIOL4CsI_tg3dIhI1HKgi2jUE5mc_REyA3SEnAg=',
			],
		];

		$config = new ChainConfig(
			new EncryptedConfig($rawConfig1, $crypto),
			new EncryptedConfig($rawConfig2, $crypto),
		);

		$this->assertSame('Secret value', $config->getString('nested.random.secret1'));
		$this->assertSame('New Secret value', $config->getString('nested.random.new-key'));
	}

	public function testKeyCollectionReturningNewestKeyIfConfigIsMissingMetaData(): void
	{

		$keyCollection = new KeyCollection(
			$this->loadKey(self::KEY_1),
			$this->loadKey(self::KEY_2),
		);
		$crypto = $this->getCrypto()->withKey($keyCollection);
		$rawConfig1 = [
			'nested' => [
				'random' => [
					'new-key' => new \Starburst\EncryptedConfigLoader\Values\EncryptedValue('MUIFAArDaF_wsQ9a435CvelFIkFFZJDPuOLEZpIO9lyLCV-k05wCYTAlJX1InTNXhXJ8g5Zl_ERAfx2Sd1__Y0-aL47n-Mx_RM89GGm9J3HzObYyB4XLpMEywYKY6V9VhugLEe0x2FpghQixRi2CZCfMMNPcsMLM_1ASiK5-rWfEouY4e3wBDMSa0-0='),
				],
			],
		];
		$rawConfig2 = [
			'override' => 42,
			'secret' => new \Starburst\EncryptedConfigLoader\Values\EncryptedValue('MUIFABIrEuQeF97ULjnCoFZB2mcpgDOmc-mV2IHtR87TiH5oZywGIBXyi6epyN1DSIqaURhhsMfXVxzS4O_qLTYDHGkk2ehTfG4B9bTDR_TtszWxy6hh-ExmdNOxWKy_IEydevhm3cK4eAoj1IPBKejam8-pZaj-iSuF6DiPPsh_dtzZhTuMLg=='),
			'nested' => [
				'random' => [
					'secret1' => new \Starburst\EncryptedConfigLoader\Values\EncryptedValue('MUIFAMCAHYllJ7gVcCGyuv8Qom7azz7toGGpFlnMQix4d5iPTSxGnAoVWA5r0nFhxIiYv2ZKgdWRG4AEkwg8dRVGOjln2ATn0c1gfJW33MX21j6Z7x4VK2dBbc3k6MlccveOa5weiHF4oYw8AygmAOxx07yVHQFSpjOjoLqVQT9JiNqninRv3A=='),
				],
			],
			'__CRYPTO_META' => [
				'tenant' => '3019f12a-cb55-4362-8cd5-b7efb7aec3f2',
				'__KEY_VERSION' => 'MUIFABhtMPMgSqmy_obWV0lmfPdzCA5PTrRurvlQED9vPa4sc7AIqBHlmHNg2RVOZG7Uc9uwLuEuMSZvBFPUoksTg33qZXObcTd8EOZoW-_-4LzPwg6NPjTIkUEEVTtzbIOL4CsI_tg3dIhI1HKgi2jUE5mc_REyA3SEnAg=',
			],
		];

		$config = new ChainConfig(
			new EncryptedConfig($rawConfig1, $crypto),
			new EncryptedConfig($rawConfig2, $crypto),
		);

		$this->assertSame('Secret value', $config->getString('nested.random.secret1'));
		$this->assertSame('New Secret value', $config->getString('nested.random.new-key'));
		$this->assertSame('Secret value', $config->getString('secret'));
		$this->assertSame(42, $config->getInt('override'));
	}
}
