<?php

namespace AWSD\Exception;

/**
 * Class AutoloadException
 *
 * Exception thrown when the autoloader fails to resolve or load a class or file.
 *
 * This exception is useful for debugging missing classes, incorrect namespaces,
 * or misconfigured PSR-4 autoloading paths.
 *
 * @package AWSD\Exception
 */
class AutoloadException extends AbstractException {}
