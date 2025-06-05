<?php

namespace AWSD\Exception;

use RuntimeException;

abstract class AbstractException extends RuntimeException
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
