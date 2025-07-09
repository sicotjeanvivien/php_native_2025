<?php

declare(strict_types=1);

namespace AWSD\Router;

use AWSD\Middleware\MiddlewareManager;

/**
 * Class Router
 *
 * A simple router for handling HTTP requests and dispatching them to appropriate actions.
 * Supports route registration, middleware execution, and dynamic URI matching.
 *
 * @package AWSD\Router
 */
class Router
{
  /**
   * @var array The list of registered routes.
   */
  private array $routes;

  /**
   * Router constructor.
   *
   * @param array $routes The initial routes to register.
   */
  public function __construct(array $routes = [])
  {
    $this->routes = [];
    $this->registerRoutes($routes);
  }

  /**
   * Adds a GET route to the router.
   *
   * @param string $path The path of the route.
   * @param mixed $action The action to be executed when the route is matched.
   */
  public function get(string $path, mixed $action): void
  {
    $this->addRoute('GET', $path, $action);
  }

  /**
   * Adds a HEAD route to the router.
   *
   * @param string $path The path of the route.
   * @param mixed $action The action to be executed when the route is matched.
   */
  public function head(string $path, mixed $action): void
  {
    $this->addRoute('HEAD', $path, $action);
  }

  /**
   * Adds a POST route to the router.
   *
   * @param string $path The path of the route.
   * @param mixed $action The action to be executed when the route is matched.
   */
  public function post(string $path, mixed $action): void
  {
    $this->addRoute('POST', $path, $action);
  }

  /**
   * Adds a PUT route to the router.
   *
   * @param string $path The path of the route.
   * @param mixed $action The action to be executed when the route is matched.
   */
  public function put(string $path, mixed $action): void
  {
    $this->addRoute('PUT', $path, $action);
  }

  /**
   * Adds a DELETE route to the router.
   *
   * @param string $path The path of the route.
   * @param mixed $action The action to be executed when the route is matched.
   */
  public function delete(string $path, mixed $action): void
  {
    $this->addRoute('DELETE', $path, $action);
  }

  /**
   * Adds a PATCH route to the router.
   *
   * @param string $path The path of the route.
   * @param mixed $action The action to be executed when the route is matched.
   */
  public function patch(string $path, mixed $action): void
  {
    $this->addRoute('PATCH', $path, $action);
  }

  /**
   * Dispatches the HTTP request to the matching route.
   *
   * Resolves the route based on method and URI, then executes the middleware pipeline.
   * If a match is found, the associated action is executed.
   * If no route matches, a HttpException is thrown.
   *
   * @param string $method The HTTP method (e.g., GET, POST).
   * @param string $request_uri The request URI.
   * @throws \AWSD\Exception\HttpException If no route matches.
   */
  public function dispatch(string $method, string $request_uri): void
  {
    $routeData = $this->resolveRoute($method, $request_uri);
    if (!$routeData) {
      throw new \AWSD\Exception\HttpException('The requested route was not found: ' . $request_uri, 404);
    }

    [$route, $params] = $routeData;
    MiddlewareManager::run(
      $route->getMiddlewares(),
      fn($request) => $this->executeAction($route->getAction(), $params)
    );
  }

  /**
   * Registers a list of routes from configuration.
   *
   * Each route should define its HTTP method, URI path, associated controller/action,
   * and optionally an array of middlewares.
   *
   * @param array $routes The route definitions.
   * @throws \InvalidArgumentException If a route is malformed or the method is unsupported.
   */
  private function registerRoutes(array $routes): void
  {
    foreach ($routes as $route) {
      $method = strtoupper($route['method'] ?? '');
      $path = $route['path'] ?? null;
      $controller = array_key_exists('controller', $route) ? 'App\Controller\\' . $route['controller'] : null;
      $action = $controller ? [$controller, $route['action']] : fn() => print($route['action']);
      $middlewares = $route['middlewares'] ?? [];

      if (!in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD'], true)) {
        throw new \InvalidArgumentException('The HTTP method is not supported: ' . $method);
      }

      if (!$path || !$action) {
        throw new \InvalidArgumentException('Malformed route: missing path or action');
      }

      $this->addRoute($method, $path, $action, $middlewares);
    }
  }

  /**
   * Adds a new route to the router.
   *
   * @param string $method The HTTP method (GET, POST, etc.).
   * @param string $path The route URI.
   * @param mixed $action The action to execute when the route is matched.
   * @param array $middleware The middleware list for this route.
   * @throws \AWSD\Exception\HttpException If the action is not callable.
   */
  private function addRoute(string $method, string $path, mixed $action, array $middleware = []): void
  {
    if (!is_callable($action) && !is_array($action)) {
      throw new \AWSD\Exception\HttpException('The provided action is not callable', 405);
    }
    $this->routes[] = new Route($method, $path, $action, $middleware);
  }

  /**
   * Resolves the route matching the given method and URI.
   *
   * If a match is found, it returns an array with the Route instance and extracted parameters.
   * If no match is found, returns null.
   *
   * @param string $method The HTTP method.
   * @param string $request_uri The request URI.
   * @return array|null Array with [Route $route, array $params] or null if not found.
   */
  private function resolveRoute(string $method, string $request_uri): ?array
  {
    $params = [];

    $matchingRoute = array_find($this->routes, function (Route $route) use ($method, $request_uri, &$params) {
      if ($route->getMethod() !== $method) {
        return false;
      }

      $match = $route->match($request_uri);
      if ($match !== false) {
        $params = $match;
        return true;
      }

      return false;
    });

    return $matchingRoute ? [$matchingRoute, $params] : null;
  }

  /**
   * Executes the action associated with a route.
   *
   * Supports closures or controller class/method pairs.
   * Parameters extracted from the URI are passed as arguments.
   *
   * @param mixed $action The action to execute (closure or [class, method]).
   * @param array $params Parameters to pass to the action.
   * @throws \AWSD\Exception\HttpException If the action is invalid or not callable.
   */
  private function executeAction(mixed $action, array $params = []): void
  {
    if (is_callable($action)) {
      call_user_func_array($action, $params);
    } elseif (is_array($action) && count($action) === 2) {
      [$class, $method] = $action;
      if (class_exists($class) && method_exists($class, $method)) {
        call_user_func_array([new $class, $method], $params);
      } else {
        throw new \AWSD\Exception\HttpException('The specified class or method does not exist', 405);
      }
    } else {
      throw new \AWSD\Exception\HttpException('Invalid action provided', 500);
    }
  }
}
