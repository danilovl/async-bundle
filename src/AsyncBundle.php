<?php declare(strict_types=1);

namespace Danilovl\AsyncBundle;

use Danilovl\AsyncBundle\DependencyInjection\AsyncExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AsyncBundle extends Bundle
{
    public function getContainerExtension(): AsyncExtension
    {
        return new AsyncExtension;
    }
}
