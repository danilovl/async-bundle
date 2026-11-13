<?php declare(strict_types=1);

namespace Danilovl\AsyncBundle\Tests\Service;

use Danilovl\AsyncBundle\Event\{
    AsyncPreCallEvent,
    AsyncPostCallEvent
};
use Danilovl\AsyncBundle\Service\AsyncService;
use Danilovl\AsyncBundle\Tests\AsyncServiceMock;
use LogicException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
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

    public function testCallEmptiesContainer(): void
    {
        $asyncService = $this->prepareAsyncService()->asyncService;
        $asyncService->call();

        $this->assertTrue($asyncService->isEmpty());
    }

    public function testCallNestedAdd(): void
    {
        $asyncService = $this->createAsyncService();
        $result = [];

        $asyncService->add(static function () use ($asyncService, &$result): void {
            $result[] = 'parent';

            $asyncService->add(static function () use (&$result): void {
                $result[] = 'nested';
            }, priority: 100);
        }, priority: 2);

        $asyncService->add(static function () use (&$result): void {
            $result[] = 'sibling';
        }, priority: 1);

        $asyncService->call();

        $this->assertSame(['parent', 'sibling', 'nested'], $result);
        $this->assertTrue($asyncService->isEmpty());
    }

    public function testCallNestedAddSortedByPriority(): void
    {
        $asyncService = $this->createAsyncService();
        $result = [];

        $asyncService->add(static function () use ($asyncService, &$result): void {
            $result[] = 'parent';

            $asyncService->add(static function () use (&$result): void {
                $result[] = 'low';
            }, priority: 1);

            $asyncService->add(static function () use (&$result): void {
                $result[] = 'high';
            }, priority: 5);
        });

        $asyncService->call();

        $this->assertSame(['parent', 'high', 'low'], $result);
    }

    public function testCallNestedAddChain(): void
    {
        $asyncService = $this->createAsyncService();
        $result = [];

        $asyncService->add(static function () use ($asyncService, &$result): void {
            $result[] = 1;

            $asyncService->add(static function () use ($asyncService, &$result): void {
                $result[] = 2;

                $asyncService->add(static function () use (&$result): void {
                    $result[] = 3;
                });
            });
        });

        $asyncService->call();

        $this->assertSame([1, 2, 3], $result);
        $this->assertTrue($asyncService->isEmpty());
    }

    public function testCallRoundsLimitNotExceeded(): void
    {
        $asyncService = $this->createAsyncService();
        $executed = 0;

        $callable = static function () use ($asyncService, &$executed, &$callable): void {
            $executed++;

            if ($executed < AsyncService::MAX_CALL_ROUNDS) {
                $asyncService->add($callable);
            }
        };

        $asyncService->add($callable);
        $asyncService->call();

        $this->assertSame(AsyncService::MAX_CALL_ROUNDS, $executed);
        $this->assertTrue($asyncService->isEmpty());
    }

    public function testCallRoundsLimitExceeded(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(AsyncPreCallEvent::class))
            ->willReturnArgument(0);

        $asyncService = new AsyncService($eventDispatcher);
        $executed = 0;

        $callable = static function () use ($asyncService, &$executed, &$callable): void {
            $executed++;
            $asyncService->add($callable);
        };

        $asyncService->add($callable);

        try {
            $asyncService->call();
            $this->fail('Expected LogicException was not thrown.');
        } catch (LogicException $exception) {
            $this->assertStringContainsString((string) AsyncService::MAX_CALL_ROUNDS, $exception->getMessage());
        }

        $this->assertSame(AsyncService::MAX_CALL_ROUNDS, $executed);
        $this->assertTrue($asyncService->isEmpty());
    }

    public function testCallStopsOnException(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(AsyncPreCallEvent::class))
            ->willReturnArgument(0);

        $asyncService = new AsyncService($eventDispatcher);
        $result = [];

        $asyncService->add(static function () use (&$result): void {
            $result[] = 'first';
        }, priority: 3);

        $asyncService->add(static function () use ($asyncService, &$result): void {
            $result[] = 'failed';

            $asyncService->add(static function () use (&$result): void {
                $result[] = 'nested';
            });

            throw new RuntimeException('fail');
        }, priority: 2);

        $asyncService->add(static function () use (&$result): void {
            $result[] = 'dependent';
        }, priority: 1);

        try {
            $asyncService->call();
            $this->fail('Expected RuntimeException was not thrown.');
        } catch (RuntimeException $exception) {
            $this->assertSame('fail', $exception->getMessage());
        }

        $this->assertSame(['first', 'failed'], $result);
        $this->assertTrue($asyncService->isEmpty());
    }

    public function testCallAfterExceptionDoesNotRepeatOldCallables(): void
    {
        $asyncService = $this->createAsyncService();
        $result = [];

        $asyncService->add(static function (): void {
            throw new RuntimeException('fail');
        }, priority: 2);

        $asyncService->add(static function () use (&$result): void {
            $result[] = 'old';
        }, priority: 1);

        try {
            $asyncService->call();
        } catch (RuntimeException) {
        }

        $asyncService->add(static function () use (&$result): void {
            $result[] = 'new';
        });

        $asyncService->call();

        $this->assertSame(['new'], $result);
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

    private function createAsyncService(): AsyncService
    {
        $eventDispatcher = $this->createStub(EventDispatcherInterface::class);

        return new AsyncService($eventDispatcher);
    }

    private function prepareAsyncService(): AsyncServiceMock
    {
        $eventDispatcher = $this->createStub(EventDispatcherInterface::class);

        return new AsyncServiceMock($eventDispatcher);
    }
}
