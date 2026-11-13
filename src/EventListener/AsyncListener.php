<?php declare(strict_types=1);

namespace Danilovl\AsyncBundle\EventListener;

use Danilovl\AsyncBundle\Service\AsyncService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

readonly class AsyncListener implements EventSubscriberInterface
{
    private const string CONSOLE_TERMINATE = 'console.terminate';
    private const string WORKER_MESSAGE_HANDLED = 'Symfony\\Component\\Messenger\\Event\\WorkerMessageHandledEvent';

    public function __construct(private AsyncService $asyncService) {}

    public function onKernelTerminate(): void
    {
        $this->asyncService->call();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::TERMINATE => ['onKernelTerminate', -1_023],
            self::CONSOLE_TERMINATE => ['onKernelTerminate', -1_023],
            self::WORKER_MESSAGE_HANDLED => ['onKernelTerminate', -1_023]
        ];
    }
}
