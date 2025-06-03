<?php

namespace AWSD\Exception;

use RuntimeException;

/**
 * Class MiddlewareException
 *
 * Exception thrown when a middleware interrupts the HTTP request processing.
 *
 * It is typically used to signal access denial, invalid requests,
 * or any other condition that prevents the request from reaching its final handler.
 *
 * @package AWSD\Exception
 */
class MiddlewareException extends RuntimeException {}
