<?php

namespace AWSD\Exception;

use RuntimeException;

/**
 * Class HttpException
 *
 * Exception thrown when an HTTP error occurs during routing or request processing.
 *
 * This exception allows attaching an HTTP status code to the error (e.g., 404, 403, 500)
 * and is used to provide structured error handling and custom error rendering.
 *
 * @package AWSD\Exception
 */
class HttpException extends RuntimeException
{
  /**
   * The HTTP status code associated with the exception.
   *
   * @var int
   */
  protected int $statusCode;

  /**
   * Constructs a new HttpException.
   *
   * @param string $message The error message.
   * @param int $statusCode The HTTP status code (default: 500).
   */
  public function __construct(string $message = "", int $statusCode = 500)
  {
    parent::__construct($message);
    $this->statusCode = $statusCode;
  }

  /**
   * Returns the HTTP status code associated with the exception.
   *
   * @return int The HTTP status code.
   */
  public function getStatusCode(): int
  {
    return $this->statusCode;
  }
}
