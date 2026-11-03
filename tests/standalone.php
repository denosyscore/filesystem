<?php

declare(strict_types=1);

use Denosys\Container\Container;
use Denosys\Container\Exceptions\ContainerResolutionException;
use Denosys\Contracts\Config\ConfigurationReaderInterface;
use Denosys\Filesystem\FilesystemInterface;
use Denosys\Filesystem\FilesystemManager;
use Denosys\Filesystem\FilesystemServiceProvider;

require dirname(__DIR__) . '/vendor/autoload.php';

function check(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$config = new class implements ConfigurationReaderInterface {
    public function get(string $key, mixed $default = null): mixed
    {
        return match ($key) {
            'filesystems.default' => 'local',
            'filesystems.disks.local' => ['driver' => 'local'],
            default => $default,
        };
    }
};

$originalDirectory = getcwd();
$directory = sys_get_temp_dir() . '/denosys-filesystem-' . bin2hex(random_bytes(6));
mkdir($directory, 0700);

try {
    $direct = new FilesystemManager($config, $directory . '/direct');
    check($direct->disk()->put('probe.txt', 'direct'), 'Direct manager write failed');
    check(file_get_contents($directory . '/direct/probe.txt') === 'direct', 'Direct manager used the wrong root');

    $container = new Container();
    $container->instance(ConfigurationReaderInterface::class, $config);
    $container->instance('path.storage', $directory . '/private');
    (new FilesystemServiceProvider())->register($container);

    $disk = $container->get(FilesystemInterface::class);
    check($disk->put('probe.txt', 'private'), 'Provider-managed disk write failed');
    check(file_get_contents($directory . '/private/app/probe.txt') === 'private', 'Provider ignored the private storage root');

    chdir($directory);
    $standalone = new Container();
    $standalone->instance(ConfigurationReaderInterface::class, $config);
    (new FilesystemServiceProvider())->register($standalone);
    check($standalone->get(FilesystemManager::class)->disk()->put('probe.txt', 'fallback'), 'Fallback disk write failed');
    check(file_get_contents($directory . '/storage/app/probe.txt') === 'fallback', 'Standalone provider used the wrong fallback root');

    $invalid = new Container();
    $invalid->instance(ConfigurationReaderInterface::class, $config);
    $invalid->instance('path.storage', '');
    (new FilesystemServiceProvider())->register($invalid);
    try {
        $invalid->get(FilesystemManager::class);
        throw new RuntimeException('An empty storage path was accepted');
    } catch (ContainerResolutionException $exception) {
        check($exception->getPrevious() instanceof InvalidArgumentException, 'Unexpected storage path error type');
        check($exception->getPrevious()->getMessage() === 'path.storage must be a non-empty string', 'Unexpected storage path error');
    }

    echo "Standalone filesystem integration passed\n";
} finally {
    chdir($originalDirectory);
    foreach (['direct/probe.txt', 'private/app/probe.txt', 'storage/app/probe.txt'] as $path) {
        if (is_file($directory . '/' . $path)) {
            unlink($directory . '/' . $path);
        }
    }
    foreach (['direct', 'private/app', 'private', 'storage/app', 'storage', ''] as $path) {
        $candidate = rtrim($directory . '/' . $path, '/');
        if (is_dir($candidate)) {
            rmdir($candidate);
        }
    }
}
