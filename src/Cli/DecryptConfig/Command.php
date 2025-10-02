<?php declare(strict_types=1);

namespace Starburst\EncryptedConfigLoader\Cli\DecryptConfig;

use Starburst\EncryptedConfigLoader\Cli\CryptoHelper;
use Symfony\Component\Console\Output\OutputInterface;

final readonly class Command
{
	public function __construct(
		private CryptoHelper $cryptoHelper,
	) {}

	public function __invoke(Input $input, OutputInterface $output): int
	{
		if ($input->key) {
			$this->cryptoHelper->setKey($input->key);
		}
		/** @var array<mixed> $config */
		$config = require $input->configFile;
		$config = $this->cryptoHelper->decryptConfig($config);

		$outputContent = $this->cryptoHelper->renderConfig(
			$config,
			$this->cryptoHelper->getDefaultDecryptedFileHeader($input->outputFile),
		);

		file_put_contents($input->outputFile, $outputContent);

		$output->writeln('Wrote decrypted config to ' . $input->outputFile);

		return 0;
	}
}
