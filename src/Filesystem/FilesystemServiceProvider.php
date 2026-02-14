<?php

declare(strict_types=1);

namespace CFXP\Core\Filesystem;

use CFXP\Core\Container\ContainerInterface;
use CFXP\Core\ServiceProviderInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Filesystem service provider.
 */
class FilesystemServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerInterface $container): void
    {
        $container->singleton(FilesystemManager::class, function (ContainerInterface $container) {
            return new FilesystemManager(
                $container->get(\CFXP\Core\Config\ConfigurationInterface::class),
                $container->get('path.storage') . '/app'
            );
        });

        // Alias for convenience
        $container->alias('filesystem', FilesystemManager::class);

        // Bind the default disk as FilesystemInterface
        $container->singleton(FilesystemInterface::class, function (ContainerInterface $container) {
            return $container->get(FilesystemManager::class)->disk();
        });
    }

    public function boot(ContainerInterface $container, ?EventDispatcherInterface $dispatcher = null): void
    {
    }
}
