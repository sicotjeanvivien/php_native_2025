<?php

namespace AWSD\Router;

class Route
{

  public function __construct(
    private string $method,
    private string $path,
    private mixed $action
  ) {
    $this->method = strtoupper($method);
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
   * Gets the path of the route.
   *
   * @return string The path.
   */
  public function getPath(): string
  {
    return $this->path;
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
   * Sets the path of the route.
   *
   * @param string $path The path.
   */
  public function setPath(string $path): void
  {
    $this->path = $path;
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

}
