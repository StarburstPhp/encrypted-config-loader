<?php declare(strict_types=1);

namespace Starburst\EncryptedConfigLoader;

use ParagonIE\Halite\Symmetric\EncryptionKey;

interface KeyResolver
{
	public function resolveKey(null|string $key): null|KeyLoader|EncryptionKey;
}
