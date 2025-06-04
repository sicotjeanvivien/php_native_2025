<?php

namespace AWSD\Middleware;

use AWSD\Middleware\MiddlewareInterface;
use AWSD\Utils\Log;

/**
 * Class LoggerMiddleware
 *
 * This class implements the MiddlewareInterface to log HTTP requests.
 */
class LoggerMiddleware implements MiddlewareInterface
{

  /**
   * Handles the HTTP request and logs it before passing it to the next middleware.
   *
   * @param mixed $request The HTTP request to handle.
   * @param mixed $next The next middleware in the stack.
   * @return mixed The response from the next middleware.
   */
  public function handle($request, $next)
  {
    Log::logRequest();
    return $next($request);
  }
}
