<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Danilovl\AsyncBundle\EventListener\AsyncListener;
use Danilovl\AsyncBundle\Service\AsyncService;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set(AsyncService::class, AsyncService::class)
        ->public();

    $container->services()
        ->set(AsyncListener::class, AsyncListener::class)
        ->autoconfigure()
        ->public();
};
