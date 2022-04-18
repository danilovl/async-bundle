<?php declare(strict_types=1);

namespace Danilovl\AsyncBundle\Tests\EventListener;

use Danilovl\AsyncBundle\EventListener\AsyncListener;
use Danilovl\AsyncBundle\Tests\AsyncServiceMock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class AsyncListenerTest extends TestCase
{
    private EventDispatcher $dispatcher;
    private AsyncServiceMock $asyncServiceMock;

    protected function setUp(): void
    {
        $this->asyncServiceMock = new AsyncServiceMock;
        $asyncListener = new AsyncListener($this->asyncServiceMock->asyncService);

        $this->dispatcher = new EventDispatcher;
        $this->dispatcher->addSubscriber($asyncListener);
    }

    public function testOnKernelTerminate(): void
    {
        $terminateEventMock = $this->createMock(KernelEvent::class);
        $counterClass = $this->asyncServiceMock->counterClass;

        $this->dispatcher->dispatch($terminateEventMock, KernelEvents::TERMINATE);

        $this->assertEquals(AsyncServiceMock::COUNTER_RESULT, $counterClass->counter);
    }
}
