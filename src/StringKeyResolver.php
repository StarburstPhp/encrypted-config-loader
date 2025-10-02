<?php declare(strict_types=1);

namespace Starburst\EncryptedConfigLoader;

use ParagonIE\ConstantTime\Hex;
use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use ParagonIE\HiddenString\HiddenString;

final class StringKeyResolver implements KeyResolver
{
	public function resolveKey(?string $key): null|EncryptionKey
	{
		if (!$key) {
			return null;
		}
		$data = Hex::decode($key);
		return new EncryptionKey(new HiddenString(
			KeyFactory::getKeyDataFromString($data)
		));
	}
}
