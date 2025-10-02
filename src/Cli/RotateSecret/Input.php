<?php declare(strict_types=1);

namespace Starburst\EncryptedConfigLoader\Cli\RotateSecret;

use Circli\Console\AbstractInput;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use Starburst\EncryptedConfigLoader\KeyLoader;

final class Input extends AbstractInput
{
	public function __construct(
		public readonly KeyLoader|EncryptionKey $oldKey,
		public readonly KeyLoader|EncryptionKey $newKey,
		public readonly string $inputFile,
	) {}
}
