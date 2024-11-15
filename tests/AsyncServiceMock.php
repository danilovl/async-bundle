<?php declare(strict_types=1);

namespace Danilovl\AsyncBundle\Tests;

use Danilovl\AsyncBundle\Service\AsyncService;

class AsyncServiceMock
{
    public const array COUNTER_RESULT = ['two', 'one', 'three', 'three'];

    public AsyncClassMock $counterClass;

    public AsyncService $asyncService;

    public function __construct()
    {
        $this->prepareAsyncService();
    }

    private function prepareAsyncService(): void
    {
        $this->counterClass = new AsyncClassMock;
        $this->asyncService = new AsyncService;

        $this->asyncService->add(function (): void {
            $this->counterClass->counter[] = 'one';
        }, priority: 2, name: 'one');

        $this->asyncService->add(function (): void {
            $this->counterClass->counter[] = 'two';
        }, priority: 3, name: 'two');

        $this->asyncService->add(function (): void {
            $this->counterClass->counter[] = 'three';
        }, priority: 1, name: 'three');

        $this->asyncService->add(function (): void {
            $this->counterClass->counter[] = 'three';
        }, priority: 1, name: 'three');
    }
}
