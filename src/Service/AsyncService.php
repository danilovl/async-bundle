<?php declare(strict_types=1);

namespace Danilovl\AsyncBundle\Service;

class AsyncService
{
    private array $container = [];

    public function add(callable $callable, int $priority = 0, string $name = null): void
    {
        $this->container[] = [
            'priority' => $priority,
            'callable' => $callable,
            'name' => $name
        ];
    }

    public function getContainer(): array
    {
        return $this->container;
    }

    public function reset(): void
    {
        $this->container = [];
    }

    public function remove(array $names, int $priority = null): void
    {
        foreach ($this->container as $key => $callable) {
            $removeCallable = false;

            if (in_array($callable['name'], $names, true)) {
                if ($priority !== null && $callable['priority'] === $priority) {
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
        $this->sort();

        foreach ($this->container as $callable) {
            $callable['callable']();
        }
    }

    private function sort(): void
    {
        usort($this->container, static fn(array $first, array $second): int => $second['priority'] <=> $first['priority']);
    }
}
