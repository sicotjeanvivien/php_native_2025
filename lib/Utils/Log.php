<?php

namespace AWSD\Utils;

use Error;

/**
 * Class Log
 *
 * This class provides methods to log error messages to the console.
 */
class Log
{
    /**
     * Logs an error message to the console with a purple color.
     *
     * This method logs an error message to the console with a timestamp, file, and line number.
     * The error message is displayed in purple color.
     *
     * @param \Throwable $error The error object to log.
     * @param string $message The custom error message to log.
     */
    public static function captureError(\Throwable $error, string $message = ""): void
    {
        $params = self::formatParams($error, $message);
        error_log(sprintf(
            "%s [%s] [ERROR: %s] \n→ Message: %s\n→ File: %s\n→ Line: %s%s",
            $params["purpleColor"],
            date("Y-m-d H:i:s"),
            $params["type"],
            $params["message"],
            $params["file"],
            $params["line"],
            $params["resetColor"]
        ));
    }

    /**
     * Formats the parameters for logging.
     *
     * This method formats the parameters for logging, including the message, file, line number, and color codes.
     *
     * @param \Throwable $e The error object.
     * @param string $message The custom error message.
     * @return array The formatted parameters.
     */
    private static function formatParams(\Throwable $e, string $message = ""): array
    {
        $color = self::useColor() ? "\033[0;35m" : "";

        return [
            "message" => $message ?: $e->getMessage(),
            "file" => $e->getFile(),
            "line" => $e->getLine(),
            "type" => get_class($e),
            "purpleColor" => $color,
            "resetColor" => $color,
        ];
    }

    /**
     * Determines if color should be used in the console output.
     *
     * This method checks if the script is running in a CLI environment and if the output is a terminal.
     *
     * @return bool True if color should be used, false otherwise.
     */
    private static function useColor(): bool
    {
        return (strpos(PHP_SAPI, 'cli') === 0) ||
            (function_exists('posix_isatty') && posix_isatty(STDOUT));
    }
}
