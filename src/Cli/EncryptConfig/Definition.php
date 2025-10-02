<?php declare(strict_types=1);

namespace Starburst\EncryptedConfigLoader\Cli\EncryptConfig;

use Circli\Console\Definition as BaseDefinition;
use ParagonIE\Halite\KeyFactory;
use Starburst\EncryptedConfigLoader\Cli\DefaultCryptoHelper;
use Starburst\EncryptedConfigLoader\DefaultCrypto;
use Starburst\EncryptedConfigLoader\FileKeyResolver;
use Starburst\EncryptedConfigLoader\KeyResolver;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

final class Definition extends BaseDefinition
{
	public function __construct(
		private readonly KeyResolver $keyResolver = new FileKeyResolver(),
	) {
		parent::__construct();
	}

	protected function configure(): void
	{
		$this->setName('config:encrypt');
		$this->setCommand(new Command(
			new DefaultCryptoHelper(new DefaultCrypto(
				KeyFactory::generateEncryptionKey(),
			)),
		));
		$this->addOption('key', 'k', InputOption::VALUE_REQUIRED, 'Key to decrypt with');
		$this->addArgument(
			'configFile',
			InputArgument::REQUIRED,
		);
		$this->addArgument(
			'outputFile',
			InputArgument::OPTIONAL,
		);
	}

	public function transformInput(InputInterface $input): InputInterface
	{
		/** @var null|string $keyInput */
		$keyInput = $input->getOption('key');
		$key = $this->keyResolver->resolveKey($keyInput);
		$configFile = $input->getArgument('configFile');
		if (!is_string($configFile) || !file_exists($configFile)) {
			throw new \InvalidArgumentException('Config file not found');
		}
		/** @var string|null $outputFile */
		$outputFile = $input->getArgument('outputFile');
		if (!$outputFile) {
			$outputFile = pathinfo($configFile, PATHINFO_DIRNAME);
			if (!$outputFile) {
				throw new \InvalidArgumentException('Failed to parse config file path');
			}
		}

		if (is_dir($outputFile)) {
			$filename = pathinfo($configFile, PATHINFO_FILENAME);
			if (str_ends_with($filename, '.decrypted')) {
				$filename = substr($filename, 0, -10);
			}
			$outputFile = $outputFile . '/' . $filename . '.php';
		}

		return new Input(
			$configFile,
			$outputFile,
			$key,
		);
	}
}
