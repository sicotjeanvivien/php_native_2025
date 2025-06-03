<?php

namespace AWSD\Middleware;

/**
 * Interface MiddlewareInterface
 *
 * This interface defines the contract for middleware components.
 * Middleware components are responsible for handling HTTP requests and responses.
 */
interface MiddlewareInterface
{
  /**
   * Handles the HTTP request and passes it to the next middleware in the stack.
   *
   * @param mixed $request The HTTP request to handle.
   * @param mixed $next The next middleware in the stack.
   * @return mixed The response from the middleware.
   */
  public function handle($request, $next);
}
