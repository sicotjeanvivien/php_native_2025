<?php

namespace AWSD\Exception;

class HttpException extends \Exception
{
  public function __construct(
    string $message = '',
    public readonly int $statusCode = 500,
    ?\Throwable $previous = null
  ) {
    parent::__construct($message, 0, $previous);
  }

  public function getStatusCode(): int
  {
    return $this->statusCode;
  }
}
