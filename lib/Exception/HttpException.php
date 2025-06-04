<?php

namespace AWSD\Exception;

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
class HttpException extends AbstractException {}
