<?php

namespace AWSD\Exception;

class HttpException extends \RuntimeException
{
  protected int $statusCode;

  public function __construct(string $message = "", int $statusCode = 500)
  {
    parent::__construct($message);
    $this->statusCode = $statusCode;
  }

  public function getStatusCode(): int
  {
    return $this->statusCode;
  }
}
