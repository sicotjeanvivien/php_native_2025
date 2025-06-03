<?php

namespace AWSD\Middleware;

use AWSD\Middleware\MiddlewareInterface;

/**
 * Class LoggerMiddleware
 *
 * This class implements the MiddlewareInterface to log HTTP requests.
 */
class LoggerMiddleware implements MiddlewareInterface
{
  /**
   * Path to the log folder.
   */
  public const FOLDER_PATH = ROOT_PATH . '/var/log/';

  /**
   * Handles the HTTP request and logs it before passing it to the next middleware.
   *
   * @param mixed $request The HTTP request to handle.
   * @param mixed $next The next middleware in the stack.
   * @return mixed The response from the next middleware.
   */
  public function handle($request, $next)
  {
    $this->ensureDirectoryExists();
    $filePath = self::FOLDER_PATH . 'request.log';
    $this->writeLog($filePath);

    return $next($request);
  }

  /**
   * Ensures that the log directory exists. Creates it if it does not.
   */
  private function ensureDirectoryExists(): void
  {
    if (!file_exists(self::FOLDER_PATH)) {
      mkdir(self::FOLDER_PATH, 0777, true);
    }
  }

  /**
   * Writes a log entry to the specified log file.
   *
   * @param string $filePath The path to the log file.
   */
  private function writeLog(string $filePath): void
  {
    $log = sprintf(
      '[%s] [Method: %s] URI: %s ;' . PHP_EOL,
      date('Y-m-d H:i:s'),
      $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
      $_SERVER['REQUEST_URI'] ?? 'UNKNOWN'
    );

    file_put_contents($filePath, $log, FILE_APPEND);
  }
}
