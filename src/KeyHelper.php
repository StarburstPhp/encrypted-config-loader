<?php declare(strict_types=1);

namespace Starburst\EncryptedConfigLoader;

use ParagonIE\ConstantTime\Hex;
use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use ParagonIE\HiddenString\HiddenString;

final class KeyHelper
{
	public static function exportKey(EncryptionKey $key): string
	{
		return KeyFactory::export($key)->getString();
	}

	public static function keyFromString(string $value): EncryptionKey
	{
		$data = Hex::decode($value);
		return new EncryptionKey(new HiddenString(
			KeyFactory::getKeyDataFromString($data)
		));
	}
}
