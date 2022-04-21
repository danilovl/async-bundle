<?php declare(strict_types=1);

namespace Danilovl\AsyncBundle\Model;

use Closure;

class CallableModel
{
    public Closure $callable;
    public int $priority = 0;
    public ?string $name;

    public function __construct(callable $callable, int $priority = 0, string $name = null)
    {
        $this->callable = $callable(...);
        $this->priority = $priority;
        $this->name = $name;
    }
}
