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
   * Path to the log folder.
   */
  public const FOLDER_PATH = ROOT_PATH . '/var/log/';

  /**
   * Logs basic HTTP request information to the request.log file.
   *
   * Includes date, method, URI, client IP, user-agent, and optional status code.
   *
   * @param int|null $statusCode The HTTP status code (default: current response code).
   */
  public static function logRequest(?int $statusCode = null): void
  {
    $log = sprintf(
      '[%s][REQUEST] METHOD: %s | URI: %s | IP: %s | AGENT: %s | STATUS: %d',
      date('Y-m-d H:i:s'),
      $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
      $_SERVER['REQUEST_URI'] ?? 'UNKNOWN',
      $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
      $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN',
      $statusCode ?? http_response_code()
    );

    self::logToFile($log);
  }


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
    $log = self::formatErrorLog($error, $message);
    self::logToFile($log);
    self::logToConsole($log);
  }

  /**
   * Formats the full error message line for file and console logging.
   *
   * Includes timestamp, HTTP status code, error type, message, file and line number.
   *
   * @param \Throwable $error The exception or error to format.
   * @param string $message Optional custom message to override the default.
   * @return string The formatted log line.
   */
  private static function formatErrorLog(\Throwable $error, string $message): string
  {
    $params = self::formatParams($error, $message);
    return sprintf(
      "[%s][ERROR] ERROR CODE: %s | TYPE: %s | MESSAGE: %s | FILE: %s | LINE: %s;",
      date("Y-m-d H:i:s"),
      http_response_code(),
      $params["type"],
      $params["message"],
      $params["file"],
      $params["line"]
    );
  }

  /**
   * Appends a log entry to the request.log file.
   *
   * Creates the /var/log directory if it does not exist.
   *
   * @param string $message The log message to write.
   */
  public  static function logToFile(string $message): void
  {
    self::ensureDirectoryExists();
    $file = self::FOLDER_PATH . 'request.log';
    file_put_contents($file, $message . PHP_EOL, FILE_APPEND);
  }

  /**
   * Outputs the log message to the PHP error console.
   *
   * The message is optionally colorized if running in CLI.
   *
   * @param string $message The message to print.
   */
  public  static function logToConsole(string $message): void
  {
    $color = self::useColor() ? "\033[0;31m" : "";
    $reset = "\033[0m";
    $message = sprintf("%s%s%s", $color, $message, $reset);
    error_log($message);
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
    return [
      "message" => $message ?: $e->getMessage(),
      "file" => $e->getFile(),
      "line" => $e->getLine(),
      "type" => get_class($e)
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

  /**
   * Ensures that the log directory exists. Creates it if it does not.
   */
  private static function ensureDirectoryExists(): void
  {
    if (!file_exists(self::FOLDER_PATH)) {
      mkdir(self::FOLDER_PATH, 0777, true);
    }
  }
}
