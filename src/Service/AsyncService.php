<?php declare(strict_types=1);

namespace Danilovl\AsyncBundle\Service;

use Danilovl\AsyncBundle\Model\CallableModel;

class AsyncService
{
    /**
     * @var CallableModel[]
     */
    private array $container = [];

    public function add(callable $callable, int $priority = 0, string $name = null): void
    {
        $this->container[] = new CallableModel($callable, $priority, $name);
    }

    public function getContainer(): array
    {
        return $this->container;
    }

    public function isEmpty(): bool
    {
        return count($this->container) === 0;
    }

    public function reset(): void
    {
        $this->container = [];
    }

    public function remove(array $names, int $priority = null): void
    {
        foreach ($this->container as $key => $callable) {
            $removeCallable = false;

            if (in_array($callable->name, $names, true)) {
                if ($priority !== null && $callable->priority === $priority) {
                    $removeCallable = true;
                } else if ($priority === null) {
                    $removeCallable = true;
                }
            }

            if ($removeCallable) {
                unset($this->container[$key]);
            }
        }
    }

    public function call(): void
    {
        call:
        $this->sort();

        foreach ($this->container as $key => $callable) {
            ($callable->callable)();

            unset($this->container[$key]);
        }

        if (!$this->isEmpty()) {
            goto call;
        }
    }

    private function sort(): void
    {
        usort($this->container, static fn(CallableModel $first, CallableModel $second): int => $second->priority <=> $first->priority);
    }
}
