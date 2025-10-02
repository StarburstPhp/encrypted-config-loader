<?php declare(strict_types=1);

namespace Starburst\EncryptedConfigLoader\Cli\RotateSecret;

use Starburst\EncryptedConfigLoader\Cli\CryptoHelper;
use Symfony\Component\Console\Output\OutputInterface;

final readonly class Command
{
	public function __construct(
		private CryptoHelper $cryptoHelper,
	) {}

	public function __invoke(Input $input, OutputInterface $output): int
	{
		$this->cryptoHelper->setKey($input->oldKey);
		/** @var array<mixed> $config */
		$config = require $input->inputFile;
		$config = $this->cryptoHelper->decryptConfig($config);

		$this->cryptoHelper->setKey($input->newKey);
		$newConfig = $this->cryptoHelper->encryptConfig($config);

		$outputContent = $this->cryptoHelper->renderConfig(
			$newConfig,
			$this->cryptoHelper->getDefaultEncryptedFileHeader($input->inputFile),
		);

		file_put_contents($input->inputFile, $outputContent);

		$output->writeln('Wrote encrypted config to ' . $input->inputFile);

		return 0;
	}
}
