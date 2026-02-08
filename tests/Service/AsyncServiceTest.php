<?php declare(strict_types=1);

namespace Danilovl\AsyncBundle\Tests\Service;

use Danilovl\AsyncBundle\Event\{
    AsyncPreCallEvent,
    AsyncPostCallEvent
};
use Danilovl\AsyncBundle\Tests\AsyncServiceMock;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AsyncServiceTest extends TestCase
{
    public function testAdd(): void
    {
        $asyncServiceMock = $this->prepareAsyncService();
        $asyncService = $asyncServiceMock->asyncService;

        $this->assertCount(4, $asyncService->getContainer());
    }

    public function testReset(): void
    {
        $asyncServiceMock = $this->prepareAsyncService();
        $asyncService = $asyncServiceMock->asyncService;
        $asyncService->reset();

        $this->assertCount(0, $asyncService->getContainer());
    }

    public function testRemove(): void
    {
        $asyncServiceMock = $this->prepareAsyncService();
        $asyncService = $asyncServiceMock->asyncService;

        $asyncService->remove(['one']);
        $this->assertCount(3, $asyncService->getContainer());

        $asyncService->remove(['two']);
        $this->assertCount(2, $asyncService->getContainer());

        $asyncService->remove(['three'], 10);
        $this->assertCount(2, $asyncService->getContainer());

        $asyncService->remove(['three'], 1);
        $this->assertCount(0, $asyncService->getContainer());
    }

    public function testCall(): void
    {
        $asyncServiceMock = $this->prepareAsyncService();
        $asyncService = $asyncServiceMock->asyncService;
        $asyncService->call();

        $class = $asyncServiceMock->counterClass;

        $this->assertEquals(AsyncServiceMock::COUNTER_RESULT, $class->counter);
    }

    public function testCallWithEvents(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $matcher = $this->exactly(2);
        $eventDispatcher->expects($matcher)
            ->method('dispatch')
            ->willReturnCallback(function (object $event, ?string $eventName = null) use ($matcher): object {
                [$expectedClass, $expectedName] = match ($matcher->numberOfInvocations()) {
                    1 => [AsyncPreCallEvent::class, null],
                    2 => [AsyncPostCallEvent::class, null],
                    default => $this->fail('Unexpected invocation'),
                };

                $this->assertInstanceOf($expectedClass, $event);
                $this->assertEquals($expectedName, $eventName);

                return $event;
            });

        $asyncServiceMock = new AsyncServiceMock($eventDispatcher);
        $asyncService = $asyncServiceMock->asyncService;

        $asyncService->call();
    }

    private function prepareAsyncService(): AsyncServiceMock
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        return new AsyncServiceMock($eventDispatcher);
    }
}
