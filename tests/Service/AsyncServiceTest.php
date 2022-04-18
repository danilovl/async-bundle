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

        $this->assertEquals(4, count($asyncService->getContainer()));
    }

    public function testReset(): void
    {
        $asyncServiceMock = $this->prepareAsyncService();
        $asyncService = $asyncServiceMock->asyncService;
        $asyncService->reset();

        $this->assertEquals(0, count($asyncService->getContainer()));
    }

    public function testRemove(): void
    {
        $asyncServiceMock = $this->prepareAsyncService();
        $asyncService = $asyncServiceMock->asyncService;

        $asyncService->remove(['one']);
        $this->assertEquals(3, count($asyncService->getContainer()));

        $asyncService->remove(['two']);
        $this->assertEquals(2, count($asyncService->getContainer()));

        $asyncService->remove(['three'], 10);
        $this->assertEquals(2, count($asyncService->getContainer()));

        $asyncService->remove(['three'], 1);
        $this->assertEquals(0, count($asyncService->getContainer()));
    }

    public function testCall(): void
    {
        $asyncServiceMock = $this->prepareAsyncService();
        $asyncService = $asyncServiceMock->asyncService;
        $asyncService->call();

        $this->assertEquals(AsyncServiceMock::COUNTER_RESULT, $asyncServiceMock->counterClass->counter);
    }

    private function prepareAsyncService(): AsyncServiceMock
    {
        return new AsyncServiceMock;
    }
}
