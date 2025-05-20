<?php

define("ROOT_PATH", dirname(__DIR__));
define("EXT_FILE", ".php");

require_once ROOT_PATH . "/lib/Autoloader.php";

use AWSD\Autoloader;
use AWSD\Router\Router;
use AWSD\View\View;
use AWSD\Exception\HttpException;

use App\Controller\HomeController;

/**
 * Initialize and register the autoloader.
 */
try {
  $aliases = ["App" => "src", "AWSD" => "lib"];
  $autoloader = new Autoloader($aliases, ROOT_PATH, EXT_FILE);
  $autoloader->register();
} catch (\Throwable $e) {
  error_log(sprintf("[%s] %s in %s:%d", date("Y-m-d H:i:s"), $e->getMessage(), $e->getFile(), $e->getLine()));
  http_response_code(500);
  echo "Internal Server Error";
  exit;
}

/**
 * Initialize and configure the router.
 */
try {
  $router = new Router();
  $router->get("/", [HomeController::class, "index"]);
  $router->get("/action", function () {
    echo "callback action";
  });


  $method = (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET');
  $uri = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
  $router->dispatch($method, $uri);
} catch (HttpException $e) {
  View::renderError($e->getStatusCode(), $e->getMessage());
  error_log(sprintf("[%s] %s in %s:%d", date("Y-m-d H:i:s"), $e->getMessage(), $e->getFile(), $e->getLine()));
  exit;
} catch (\Throwable $e) {
  View::renderError(500, "Une erreur inattendue est survenue.");
  error_log(sprintf("[%s] %s in %s:%d", date("Y-m-d H:i:s"), $e->getMessage(), $e->getFile(), $e->getLine()));
  header("Content-Type: text/plain; charset=utf-8");
  exit;
}
