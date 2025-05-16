<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use AWSD\Router\Router;
use AWSD\Exception\HttpException;
use AWSD\Utils\DummyClass;


class RouterTest extends TestCase
{
  public function testMatchingGetRouteExecutesCallable(): void
  {
    $router = new Router();

    $executed = false;
    $router->get('/test', function () use (&$executed) {
      $executed = true;
    });

    ob_start();
    $router->dispatch('GET', '/test');
    ob_end_clean();

    $this->assertTrue($executed);
  }

  public function testReturns404OnNoMatchingRoute(): void
  {
    $router = new Router();

    $this->expectException(HttpException::class);
    $this->expectExceptionMessage('Route not found: /not-found');

    ob_start();
    try {
      $router->dispatch('GET', '/not-found');
    } finally {
      ob_end_clean();
    }
  }

  public function testControllerClassMethod(): void
  {
    $router = new Router();

    // Simule un controller avec une méthode
    $router->get('/controller', [DummyClass::class, 'show']);

    ob_start();
    $router->dispatch('GET', '/controller');
    $output = ob_get_clean();

    $this->assertSame("controller called", $output);
  }

  public function testInvalidControllerThrowsException(): void
  {
    $router = new Router();

    $router->get('/invalid', [DummyClass::class, 'method']);

    $this->expectException(HttpException::class);
    $this->expectExceptionMessage('Method not allowed');

    ob_start();
    try {
      $router->dispatch('GET', '/invalid');
    } finally {
      ob_end_clean();
    }
  }
}
