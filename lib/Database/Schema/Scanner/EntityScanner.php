<?php

namespace AWSD\Database\Schema\Scanner;

final class EntityScanner
{
  public const BASE_PATH = ROOT_PATH . '/src/';
  public const BASE_NAMESPACE = 'App';

  public static function findEntities(string $directory): array
  {
    $results = [];

    $dir = new \RecursiveDirectoryIterator($directory);
    $iterator = new \RecursiveIteratorIterator($dir);

    foreach ($iterator as $file) {
      if ($file->isFile() && str_ends_with($file->getFilename(), 'Entity.php')) {
        $class = self::pathToClass($file->getPathname());
        if (class_exists($class)) {
          $results[] = new $class();
        }
      }
    }

    return $results;
  }

  private static function pathToClass(string $filepath): string
  {
    $relative = str_replace(self::BASE_PATH, '', $filepath);
    $noExt    = str_replace('.php', '', $relative);
    return self::BASE_NAMESPACE . '\\' . str_replace(DIRECTORY_SEPARATOR, '\\', $noExt);
  }
}
