<?php

namespace AWSD\Router;

/**
 * Class Router
 *
 * A simple router for handling HTTP requests and dispatching them to appropriate actions.
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
   * @throws \InvalidArgumentException If the action is not callable.
   */
  public function get(string $path, mixed $action): void
  {
    $this->addRoute("GET", $path, $action);
  }

  /**
   * Adds a HEAD route to the router.
   *
   * @param string $path The path of the route.
   * @param mixed $action The action to be executed when the route is matched.
   * @throws \InvalidArgumentException If the action is not callable.
   */
  public function head(string $path, mixed $action): void
  {
    $this->addRoute("HEAD", $path, $action);
  }

  /**
   * Adds a POST route to the router.
   *
   * @param string $path The path of the route.
   * @param mixed $action The action to be executed when the route is matched.
   * @throws \InvalidArgumentException If the action is not callable.
   */
  public function post(string $path, mixed $action): void
  {
    $this->addRoute("POST", $path, $action);
  }

  /**
   * Adds a PUT route to the router.
   *
   * @param string $path The path of the route.
   * @param mixed $action The action to be executed when the route is matched.
   * @throws \InvalidArgumentException If the action is not callable.
   */
  public function put(string $path, mixed $action): void
  {
    $this->addRoute("PUT", $path, $action);
  }

  /**
   * Adds a DELETE route to the router.
   *
   * @param string $path The path of the route.
   * @param mixed $action The action to be executed when the route is matched.
   * @throws \InvalidArgumentException If the action is not callable.
   */
  public function delete(string $path, mixed $action): void
  {
    $this->addRoute("DELETE", $path, $action);
  }

  /**
   * Adds a PATCH route to the router.
   *
   * @param string $path The path of the route.
   * @param mixed $action The action to be executed when the route is matched.
   * @throws \InvalidArgumentException If the action is not callable.
   */
  public function patch(string $path, mixed $action): void
  {
    $this->addRoute("PATCH", $path, $action);
  }

  /**
   * Dispatches the request to the appropriate route.
   *
   * @param string $method The HTTP method (e.g., GET, POST).
   * @param string $request_uri The request URI.
   * @throws \RuntimeException If the route is not found.
   */
  public function dispatch(string $method, string $request_uri): void
  {
    $route = $this->resolveRoute($method, $request_uri);
    if (!$route) {
      throw new \AWSD\Exception\HttpException("Route not found: $request_uri", 404);
    }
    $this->executeAction($route->getAction());
  }

  /**
   * Registers an array of routes.
   *
   * @param array $routes The routes to register.
   * @throws \InvalidArgumentException If a route is malformed or the HTTP method is not supported.
   */
  private function registerRoutes(array $routes): void
  {
    foreach ($routes as $route) {
      $method = strtoupper($route['method'] ?? '');
      $path = $route['path'] ?? null;
      $controller = array_key_exists("controller", $route) ? "App\Controller\\" . $route["controller"] : null;
      $action = $controller ? [$controller, $route['action']] : fn() => print($route['action']);

      if (!in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD'], true)) {
        throw new \InvalidArgumentException("Unsupported HTTP method: $method");
      }

      if (!$path || !$action) {
        throw new \InvalidArgumentException("Malformed route (missing path or action)");
      }

      $this->addRoute($method, $path, $action);
    }
  }

  /**
   * Adds a route to the router.
   *
   * @param string $method The HTTP method (e.g., GET, POST).
   * @param string $path The path of the route.
   * @param mixed $action The action to be executed when the route is matched.
   * @throws \InvalidArgumentException If the action is not callable.
   */
  private function addRoute(string $method, string $path, mixed $action): void
  {
    if (!is_callable($action) && !is_array($action)) {
      throw new \AWSD\Exception\HttpException("Method not allowed", 405);
    }
    $this->routes[] = new Route($method, $path, $action);
  }

  /**
   * Resolves the route based on the HTTP method and request URI.
   *
   * @param string $method The HTTP method.
   * @param string $request_uri The request URI.
   * @return Route|null The resolved route or null if not found.
   */
  private function resolveRoute(string $method, string $request_uri): ?Route
  {
    return array_find($this->routes, function (Route $route) use ($method, $request_uri) {
      return $route->getMethod() === $method && $route->getPath() === $request_uri;
    });
  }

  /**
   * Executes the action associated with the route.
   *
   * @param mixed $action The action to be executed.
   * @throws \RuntimeException If the action is invalid or the class/method does not exist.
   */
  private function executeAction(mixed $action): void
  {
    if (is_callable($action)) {
      call_user_func($action);
    } elseif (is_array($action) && count($action) === 2) {
      [$class, $method] = $action;
      if (class_exists($class) && method_exists($class, $method)) {
        call_user_func([new $class, $method]);
      } else {
        throw new \AWSD\Exception\HttpException("Method not allowed", 405);
      }
    } else {
      throw new \AWSD\Exception\HttpException("Invalid action.", 500);
    }
  }
}
