<?php

namespace AWSD\Router;

use AWSD\Utils\Sanitization;

/**
 * Class Route
 *
 * Represents a route in the application, including its HTTP method, URI pattern,
 * associated action (callable or controller), and attached middlewares.
 *
 * Supports dynamic route parameters using pattern placeholders like /user/{id}.
 *
 * @package AWSD\Router
 */
class Route
{
  private array $parameters = [];
  private string $pattern;

  /**
   * Constructs a new Route instance.
   *
   * @param string $method The HTTP method (GET, POST, etc.).
   * @param string $uri The URI pattern (e.g., /article/{id}).
   * @param mixed $action The action to execute when the route is matched.
   * @param array $middlewares The list of middlewares for this route.
   */
  public function __construct(
    private string $method,
    private string $uri,
    private mixed $action,
    private array $middlewares
  ) {
    $this->method = strtoupper($method);
    $this->compilePattern();
  }

  /**
   * Returns the HTTP method of the route.
   *
   * @return string
   */
  public function getMethod(): string
  {
    return $this->method;
  }

  /**
   * Returns the URI of the route.
   *
   * @return string
   */
  public function getUri(): string
  {
    return $this->uri;
  }

  /**
   * Returns the action associated with the route.
   *
   * @return mixed
   */
  public function getAction(): mixed
  {
    return $this->action;
  }

  /**
   * Returns the list of middlewares assigned to the route.
   *
   * @return array
   */
  public function getMiddlewares(): array
  {
    return $this->middlewares;
  }

  /**
   * Sets the HTTP method of the route.
   *
   * @param string $method
   */
  public function setMethod(string $method): void
  {
    $this->method = strtoupper($method);
  }

  /**
   * Sets the URI of the route.
   *
   * @param string $uri
   */
  public function setUri(string $uri): void
  {
    $this->uri = $uri;
    $this->compilePattern();
  }

  /**
   * Sets the action of the route.
   *
   * @param mixed $action
   */
  public function setAction(mixed $action): void
  {
    $this->action = $action;
  }

  /**
   * Sets the list of middlewares for the route.
   *
   * @param array $middlewares
   */
  public function setMiddlewares(array $middlewares): void
  {
    $this->middlewares = $middlewares;
  }

  /**
   * Matches the incoming request URI with the route pattern.
   * If matched, returns an associative array of extracted parameters.
   *
   * @param string $requestUri
   * @return array|false
   */
  public function match(string $requestUri): false|array
  {
    if (preg_match($this->pattern, $requestUri, $matches)) {
      array_shift($matches);
      $matches = Sanitization::clean($matches);
      return array_combine($this->parameters, $matches);
    }

    return false;
  }

  /**
   * Compiles the route URI into a regex pattern for matching.
   *
   * @throws \RuntimeException If the pattern could not be compiled.
   */
  private function compilePattern(): void
  {
    $this->pattern = preg_replace_callback(
      '#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#',
      function ($matches) {
        $this->parameters[] = $matches[1];
        return '([^/]+)';
      },
      $this->uri
    );

    if ($this->pattern === null) {
      throw new \RuntimeException('Failed to compile the route pattern for URI: ' . $this->uri);
    }

    $this->pattern = '#^' . $this->pattern . '$#';
  }
}
