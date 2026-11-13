<?php declare(strict_types=1);

namespace Danilovl\AsyncBundle\Tests\EventListener;

use Danilovl\AsyncBundle\EventListener\AsyncListener;
use Danilovl\AsyncBundle\Tests\AsyncServiceMock;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\EventDispatcher\{
    Event,
    EventDispatcherInterface
};

class AsyncListenerTest extends TestCase
{
    private EventDispatcher $dispatcher;

    private AsyncServiceMock $asyncServiceMock;

    protected function setUp(): void
    {
        $eventDispatcher = $this->createStub(EventDispatcherInterface::class);
        $this->asyncServiceMock = new AsyncServiceMock($eventDispatcher);
        $asyncListener = new AsyncListener($this->asyncServiceMock->asyncService);

        $this->dispatcher = new EventDispatcher;
        $this->dispatcher->addSubscriber($asyncListener);
    }

    public function testOnKernelTerminate(): void
    {
        $terminateEventMock = $this->createStub(KernelEvent::class);
        $class = $this->asyncServiceMock->counterClass;

        $this->dispatcher->dispatch($terminateEventMock, KernelEvents::TERMINATE);

        $this->assertEquals(AsyncServiceMock::COUNTER_RESULT, $class->counter);
    }

    #[DataProvider('provideCallOnTerminateEventsCases')]
    public function testCallOnTerminateEvents(string $eventName): void
    {
        $class = $this->asyncServiceMock->counterClass;

        $this->dispatcher->dispatch(new Event, $eventName);

        $this->assertEquals(AsyncServiceMock::COUNTER_RESULT, $class->counter);
        $this->assertTrue($this->asyncServiceMock->asyncService->isEmpty());
    }

    public static function provideCallOnTerminateEventsCases(): iterable
    {
        yield 'kernel.terminate' => [KernelEvents::TERMINATE];
        yield 'console.terminate' => ['console.terminate'];
        yield 'messenger worker message handled' => ['Symfony\\Component\\Messenger\\Event\\WorkerMessageHandledEvent'];
    }
}
