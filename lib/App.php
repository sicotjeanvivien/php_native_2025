<?php

namespace AWSD;

use AWSD\Utils\Env;
use AWSD\Router\Router;
use AWSD\Template\View;
use AWSD\Exception\HttpException;
use AWSD\Router\Route;
use AWSD\Utils\Log;

/**
 * Class App
 *
 * This class is responsible for bootstrapping the application.
 */
class App
{
  /**
   * Bootstraps the application by initializing the environment and the router.
   */
  public static function bootstrap(): void
  {
    self::initEnv();
    self::initRouter();
  }

  /**
   * Initializes the environment by loading the .env file.
   */
  private static function initEnv(): void
  {
    new Env();
  }

  /**
   * Initializes the router and sets up the routes.
   *
   * @throws HttpException If there is an error during routing.
   */
  private static function initRouter(): void
  {
    $routesConfig = json_decode(file_get_contents(ROOT_PATH . "/config/routes.json"), true);
    $router = new Router($routesConfig);

    $router->get("/", [\App\Controller\HomeController::class, "index"]);
    $router->get("/action", fn() => print("callback action"));

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

    try {
      $router->dispatch($method, $uri);
    } catch (HttpException $e) {
      View::renderError($e->getStatusCode(), $e->getMessage());
      Log::captureError($e);
    }
  }
}
