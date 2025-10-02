<?php declare(strict_types=1);

namespace Starburst\EncryptedConfigLoader\Cli\GenerateSecret;

use ParagonIE\Halite\KeyFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Command extends \Circli\Console\Definition
{
	protected function configure(): void
	{
		$this->setName('config:generate-secret');
		$this->setCommand(function (InputInterface $input, OutputInterface $output) {
			$output->writeln("<info>Generating secret</info>\n" . PHP_EOL);

			$key = KeyFactory::export(KeyFactory::generateEncryptionKey());

			$output->writeln('<info>Secret:</info>');
			$output->writeln(sprintf('%s', $key->getString()));

			return 0;
		});
	}
}
