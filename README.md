[![phpunit](https://github.com/danilovl/async-bundle/actions/workflows/phpunit.yml/badge.svg)](https://github.com/danilovl/async-bundle/actions/workflows/phpunit.yml)
[![downloads](https://img.shields.io/packagist/dt/danilovl/async-bundle)](https://packagist.org/packages/danilovl/async-bundle)
[![latest Stable Version](https://img.shields.io/packagist/v/danilovl/async-bundle)](https://packagist.org/packages/danilovl/async-bundle)
[![license](https://img.shields.io/packagist/l/danilovl/async-bundle)](https://packagist.org/packages/danilovl/async-bundle)

# AsyncBundle #

## About ##

Symfony bundle provides simple delayed function call in `AsyncListener` after symfony send response.  

The user gets a response faster because all unnecessary logic is processed later. For example: logging, creating rabbitmq queues or other unnecessary things.

![Alt text](/.github/readme/profiler.png?raw=true "Profiler")

### Requirements 

  * PHP 8.1.0 or higher
  * Symfony 6.0 or higher

### 1. Installation

Install `danilovl/async-bundle` package by Composer:
 
``` bash
$ composer require danilovl/async-bundle
```
Add the `AsyncBundle` to your application's bundles if does not add automatically:

```php
<?php
// config/bundles.php

return [
    // ...
    Danilovl\AsyncBundle\AsyncBundle::class => ['all' => true]
];
```

### 2. Usage

`AsyncService` has simple three methods `add`, `remove` and `reset`.

```php
<?php declare(strict_types=1);

namespace App\Controller;

use Danilovl\AsyncBundle\Attribute\PermissionMiddleware;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class HomeController extends AbstractController
{
    public function __construct(private AsyncService $asyncService)
    {
    }

    public function index(Request $request): Response
    {
        $this->asyncService->add(function () {
            // add callback with priority 0 and without name
        });

        $this->asyncService->add(function () {
            // add callback with priority 10
            // higher means sooner
        }, 10);    
        
        $this->asyncService->add(function () {
            // add callback with priority -10
            // less means later
        }, -10);    
        
        $this->asyncService->add(function () {
            // add callback with priority and name
        }, 90, 'sendEmail');     
        
        $this->asyncService->add(function () {
            // add second callback with priority and same name
        }, 100, 'sendEmail');
        
        // remove all callbacks with name 'sendEmail'
        $this->asyncService->remove(['sendEMail']);        
        
        // remove all callbacks with name 'sendEmail' and priority 
        $this->asyncService->remove(['sendEMail'], 100);    
        
        // remove all callbacks
        $this->asyncService->reset();

        return $this->render('home/index.html.twig');
    }
}
```

## License

The AsyncBundle is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
