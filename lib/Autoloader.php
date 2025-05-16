<?php

namespace AWSD;

use AWSD\Exception\AutoloadException;

class Autoloader
{
  private array $aliases = [];
  private string $rootPath;
  private string $fileExtension;

  /**
   * Autoloader constructor.
   * @param array $aliases The aliases for namespaces.
   * @param string $rootPath The root path for the project.
   * @param string $fileExtension The file extension for PHP files.
   */
  public function __construct(array $aliases, string $rootPath = '', string $fileExtension = '.php')
  {
    $this->aliases = $aliases;
    $this->rootPath = $rootPath ?: __DIR__;
    $this->fileExtension = $fileExtension;
  }

  /**
   * Register the autoloader with SPL autoload.
   */
  public function register(): void
  {
    spl_autoload_register([$this, 'loadClass']);
  }

  /**
   * Load the class file.
   * @param string $class The fully qualified class name.
   * @throws \Exception If the file is not found or the namespace is invalid.
   */
  private function loadClass(string $class): void
  {
    $filePath = $this->namespaceTofilePath($class);
    if (!file_exists($filePath)) {
      throw new AutoloadException("File « $filePath » not found for class « $class ».");
    }

    require_once $filePath;
  }

  /**
   * Convert a namespace to a file path.
   * @param string $class The fully qualified class name.
   * @return string The file path.
   * @throws \Exception If the namespace is invalid.
   */
  private function namespaceTofilePath(string $class): string
  {
    $parts = explode("\\", $class);

    if (!$this->hasAlias($parts[0])) {
      throw new \Exception("Namespace « {$parts[0]} » is invalid. Allowed: " . implode(", ", array_keys($this->aliases)));
    }

    $parts[0] = $this->aliases[$parts[0]];
    return $this->rootPath . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $parts) . $this->fileExtension;
  }

  /**
   * Check if an alias exists.
   * @param string $alias The alias to check.
   * @return bool True if the alias exists, false otherwise.
   */
  private function hasAlias(string $alias): bool
  {
    return array_key_exists($alias, $this->aliases);
  }
}
