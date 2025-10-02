<?php declare(strict_types=1);

namespace Starburst\EncryptedConfigLoader\Cli\DecryptConfig;

use Circli\Console\AbstractInput;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use Starburst\EncryptedConfigLoader\KeyLoader;

final class Input extends AbstractInput
{
	public function __construct(
		public readonly string $configFile,
		public readonly string $outputFile,
		public readonly null|KeyLoader|EncryptionKey $key,
	) {}
}
