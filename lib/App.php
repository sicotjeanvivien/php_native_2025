<?php

namespace AWSD;

use AWSD\Utils\Env;
use AWSD\Router\Router;
use AWSD\Template\View;
use AWSD\Exception\HttpException;
use AWSD\Exception\MiddlewareException;
use AWSD\Utils\Log;
use AWSD\Utils\Sanitization;

/**
 * Class App
 *
 * This class is responsible for bootstrapping the application.
 * It initializes the environment configuration, sanitizes global input,
 * sets up the router, and dispatches the HTTP request.
 * 
 * It also handles global exception management for routing and middleware execution.
 */
class App
{
  /**
   * Bootstraps the application by initializing the environment and the router.
   * Also applies global sanitization to user input ($_GET, $_POST, etc.).
   */
  public static function bootstrap(): void
  {
    self::initEnv();
    Sanitization::sanitizeGlobals();
    self::initRouter();
  }

  /**
   * Initializes the environment by loading environment variables
   * from the .env file using the Env utility class.
   */
  private static function initEnv(): void
  {
    new Env();
  }

  /**
   * Initializes the router and dispatches the current HTTP request.
   *
   * It loads the routes from the configuration file,
   * creates the router instance, and dispatches the matched route.
   * 
   * Exceptions are caught and rendered:
   * - HttpException: for routing or method issues (404, 405, etc.)
   * - MiddlewareException: for access-related errors thrown by middleware (403)
   *
   * @throws HttpException If the route cannot be resolved.
   */
  private static function initRouter(): void
  {
    $routesConfig = json_decode(file_get_contents(ROOT_PATH . '/config/routes.json'), true);
    $router = new Router($routesConfig);

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

    try {
      $router->dispatch($method, $uri);
    } catch (HttpException $e) {
      View::renderError($e->getStatusCode(), $e->getMessage());
      Log::captureError($e);
    } catch (MiddlewareException $e) {
      View::renderError(403, $e->getMessage());
      Log::captureError($e);
    }
  }
}
