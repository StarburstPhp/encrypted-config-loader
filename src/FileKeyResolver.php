<?php declare(strict_types=1);

namespace Starburst\EncryptedConfigLoader;

use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Symmetric\EncryptionKey;

final class FileKeyResolver implements KeyResolver
{
	public function resolveKey(null|string $key): null|EncryptionKey
	{
		if (!$key) {
			return null;
		}
		try {
			return KeyFactory::loadEncryptionKey($key);
		}
		catch (\Throwable) {
			return null;
		}
	}
}
