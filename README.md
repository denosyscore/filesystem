# denosyscore/filesystem

Filesystem management and disk abstractions

## Installation

composer require denosyscore/filesystem

Version 0.3 requires `denosyscore/contracts` 0.2.4 or newer within the 0.2
series and installs `denosyscore/container` directly for its service provider.

## Configuration

Bind a `Denosys\Contracts\Config\ConfigurationReaderInterface` implementation
before registering `FilesystemServiceProvider`. The interface has one method:
`get(string $key, mixed $default = null): mixed`. The provider reads
`filesystems.default` and `filesystems.disks.<name>` from that object.

```php
use Denosys\Container\Container;
use Denosys\Contracts\Config\ConfigurationReaderInterface;
use Denosys\Filesystem\FilesystemInterface;
use Denosys\Filesystem\FilesystemServiceProvider;

$container = new Container();
$container->instance(ConfigurationReaderInterface::class, $configuration);
$container->instance('path.storage', '/private/storage'); // optional
(new FilesystemServiceProvider())->register($container);

$disk = $container->get(FilesystemInterface::class);
$disk->put('example.txt', 'Stored on the default disk');
```

When `path.storage` is bound, the provider's default local root is its `app`
subdirectory. Without it, the manager uses `getcwd() . '/storage/app'`.
An explicit disk `root` setting takes precedence in both cases. Direct
`FilesystemManager` callers must pass an implementation of the shared read
contract starting with 0.3.

## Included Modules

- src/Filesystem/*

## Development

composer validate --strict
find src -type f -name '*.php' -print0 | xargs -0 -n1 php -l
composer test

## CI Workflows

- CI: composer validation, PHP syntax lint, and standalone integration on PHP 8.2 and 8.5.
- Release: GitHub release publication on semantic version tags.
- Dependabot: weekly Composer dependency update checks.
