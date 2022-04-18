<?php declare(strict_types=1);

namespace Danilovl\AsyncBundle\EventListener;

use Danilovl\AsyncBundle\Service\AsyncService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class AsyncListener implements EventSubscriberInterface
{
    public function __construct(private AsyncService $asyncService)
    {
    }

    public function onKernelTerminate(): void
    {
        $this->asyncService->call();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::TERMINATE => ['onKernelTerminate', 99999]
        ];
    }
}
