<?php

namespace AWSD\Router;

class Route
{
  private array $parameters = [];
  private string $pattern;

  /**
   * Constructor for the Route class.
   *
   * @param string $method The HTTP method for the route.
   * @param string $uri The URI pattern for the route.
   * @param mixed $action The action to be executed when the route is matched.
   */
  public function __construct(
    private string $method,
    private string $uri,
    private mixed $action
  ) {
    $this->method = strtoupper($method);
    $this->compilePattern();
  }

  /**
   * Gets the HTTP method of the route.
   *
   * @return string The HTTP method.
   */
  public function getMethod(): string
  {
    return $this->method;
  }

  /**
   * Gets the URI of the route.
   *
   * @return string The URI.
   */
  public function getUri(): string
  {
    return $this->uri;
  }

  /**
   * Gets the action of the route.
   *
   * @return mixed The action.
   */
  public function getAction(): mixed
  {
    return $this->action;
  }

  /**
   * Sets the HTTP method of the route.
   *
   * @param string $method The HTTP method.
   */
  public function setMethod(string $method): void
  {
    $this->method = strtoupper($method);
  }

  /**
   * Sets the URI of the route.
   *
   * @param string $uri The URI.
   */
  public function setUri(string $uri): void
  {
    $this->uri = $uri;
    $this->compilePattern();
  }

  /**
   * Sets the action of the route.
   *
   * @param mixed $action The action.
   */
  public function setAction(mixed $action): void
  {
    $this->action = $action;
  }

  /**
   * Matches the request URI against the route pattern.
   *
   * @param string $requestUri The request URI to match.
   * @return false|array Returns an array of parameters if matched, false otherwise.
   */
  public function match(string $requestUri): false|array
  {
    if (preg_match($this->pattern, $requestUri, $matches)) {
      array_shift($matches);
      return array_combine($this->parameters, $matches);
    }

    return false;
  }

  /**
   * Compiles the URI pattern for regex matching.
   *
   * @throws \RuntimeException If the pattern compilation fails.
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
      throw new \RuntimeException("Failed to compile the route pattern for URI: " . $this->uri);
    }

    $this->pattern = "#^" . $this->pattern . "$#";
  }
}
