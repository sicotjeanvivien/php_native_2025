<?php

namespace AWSD\Middleware;

/**
 * Class MiddlewareManager
 *
 * This class manages the execution of middleware in a pipeline.
 */
class MiddlewareManager
{
  /**
   * @var array The list of global middleware classes.
   */
  protected static array $globalMiddleware = [
    \AWSD\Middleware\LoggerMiddleware::class,
  ];

  /**
   * Runs the middleware pipeline and executes the final action.
   *
   * @param array $middlewares The list of middleware classes to run.
   * @param callable $finalAction The final action to execute after all middleware.
   * @return mixed The result of the final action.
   */
  public static function run(array $middlewares, callable $finalAction): mixed
  {
    $allMiddlewares = [...self::$globalMiddleware, ...$middlewares];
    $middlewareInstances = array_map(fn($class) => new $class(), $allMiddlewares);

    $pipeline = array_reduce(
      array_reverse($middlewareInstances),
      fn($next, $middleware) => fn($request) => $middleware->handle($request, $next),
      $finalAction
    );

    return $pipeline(null);
  }
}
