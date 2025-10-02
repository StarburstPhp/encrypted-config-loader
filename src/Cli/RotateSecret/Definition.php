<?php declare(strict_types=1);

namespace Starburst\EncryptedConfigLoader\Cli\RotateSecret;

use ParagonIE\Halite\KeyFactory;
use Starburst\EncryptedConfigLoader\Cli\DefaultCryptoHelper;
use Starburst\EncryptedConfigLoader\DefaultCrypto;
use Starburst\EncryptedConfigLoader\FileKeyResolver;
use Starburst\EncryptedConfigLoader\KeyResolver;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

final class Definition extends \Circli\Console\Definition
{
	public function __construct(
		private readonly KeyResolver $keyResolver = new FileKeyResolver(),
	) {
		parent::__construct();
	}

	protected function configure(): void
	{
		$this->setName('config:rotate-secret');
		$this->setCommand(new Command(
			new DefaultCryptoHelper(new DefaultCrypto(
				KeyFactory::generateEncryptionKey(),
			)),
		));
		$this->addArgument(
			'configFile',
			InputArgument::REQUIRED,
		);
		$this->addArgument(
			'oldKey',
			InputArgument::REQUIRED,
		);
		$this->addArgument(
			'newKey',
			InputArgument::REQUIRED,
		);
	}

	public function transformInput(InputInterface $input): InputInterface
	{
		/** @var string $newKeyInput */
		$newKeyInput = $input->getArgument('newKey');
		/** @var string $oldKeyInput */
		$oldKeyInput = $input->getArgument('oldKey');
		$newKey = $this->keyResolver->resolveKey($newKeyInput);
		if (!$newKey) {
			throw new \InvalidArgumentException('Failed to load new encryption key');
		}
		$oldKey = $this->keyResolver->resolveKey($oldKeyInput);
		if (!$oldKey) {
			throw new \InvalidArgumentException('Failed to load old encryption key');
		}
		$configFile = $input->getArgument('configFile');
		if (!is_string($configFile) || !file_exists($configFile)) {
			throw new \InvalidArgumentException('Config file not found');
		}
		return new Input(
			$oldKey,
			$newKey,
			$configFile,
		);
	}
}
