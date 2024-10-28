<?php declare(strict_types=1);

namespace Danilovl\AsyncBundle\Tests\Service;

use Danilovl\AsyncBundle\Tests\AsyncServiceMock;
use PHPUnit\Framework\TestCase;

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

        /** @var object{counter: array} $class */
        $class = $asyncServiceMock->counterClass;

        $this->assertEquals(AsyncServiceMock::COUNTER_RESULT, $class->counter);
    }

    private function prepareAsyncService(): AsyncServiceMock
    {
        return new AsyncServiceMock;
    }
}
