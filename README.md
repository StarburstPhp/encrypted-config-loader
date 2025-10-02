# Encrypted config loader

Load encrypted config files with support for key rotation

## Requirements

PHP 8.2 or higher.

## Installation

```bash
composer require starburst/encrypted-config-loader
```

### Setup in starburst

It's on purpose that we don't provide a default `Bootloader` for these since most project should add their own logic
for key resolving and key rotating 

So you need to write your own `Bootloader` but here is a basic example:

```php

class EncryptionConfigCliBootloader implements 
	\Starburst\Contracts\Bootloader,
	\Starburst\Contracts\Extensions\CliCommandProvider,
	\Starburst\Contracts\Extensions\DefinitionProvider
 {
	public function createDefinitionSource(): \Stefna\DependencyInjection\Definition\DefinitionSource
	{
		return new \Stefna\DependencyInjection\Definition\DefinitionArray([
			\Starburst\EncryptedConfigLoader\KeyResolver::class => fn () => new \Starburst\EncryptedConfigLoader\FileKeyResolver(), // if you store the key in an external system you need to write your own KeyResolver. This can also be used to provide a default key for the cli commands
			\Starburst\EncryptedConfigLoader\KeyLoader::class => fn () => new KeyCollection(), // if you have multiple keys
			\ParagonIE\Halite\Symmetric\EncryptionKey::class => fn () => \ParagonIE\Halite\KeyFactory::loadEncryptionKey('path to encryption key'), // if you only have one key this is the way to go 
			\Starburst\EncryptedConfigLoader\Crypto::class => fn (\Psr\Container\ContainerInterface $c) => new \Starburst\EncryptedConfigLoader\DefaultCrypto(
				$c->get(\Starburst\EncryptedConfigLoader\KeyLoader::class), 
				$c->get(\ParagonIE\Halite\Symmetric\EncryptionKey::class), 
			),
		]);
	}

}

```

## Contribute

We are always happy to receive bug/security reports and bug/security fixes

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
