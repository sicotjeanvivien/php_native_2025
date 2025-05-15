<?php

namespace AWSD;

class Autoloader
{

  private array $aliases = [];

  public function __construct(array $aliases)
  {
    $this->aliases = $aliases;
  }

  public function register(): void
  {
    spl_autoload_register([$this, 'loadClass']);
  }

  private function loadClass(string $class): void
  {
    $filepath = $this->namespaceToFilepath($class);
    if (!file_exists($filepath)) {
      throw new \Exception("Fichier « $filepath » introuvable pour la classe « $class ».");
    }

    require_once $filepath;
  }

  private function namespaceToFilepath(string $class): string
  {
    $parts = explode("\\", $class);

    if (!$this->hasAlias($parts[0])) {
      throw new \Exception("Namespace « {$parts[0]} » invalide. Autorisés : " . implode(", ", array_keys($this->aliases)));
    }

    $parts[0] = $this->aliases[$parts[0]];
    return ROOT_PATH . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $parts) . EXT_FILE;
  }

  private function hasAlias(string $alias): bool
  {
    return array_key_exists($alias, $this->aliases);
  }
}