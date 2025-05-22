<?php

namespace AWSD\Template;

/**
 * Class Partial
 *
 * This class provides methods to include and render partial templates.
 */
class Partial extends Template
{
  /**
   * @var array Caches the paths of partial templates.
   */
  private static array $cache = [];

  /**
   * Includes a partial template with the given parameters.
   *
   * This method takes a partial template name and an array of parameters, then renders the partial template.
   *
   * @param string $partialName The name of the partial template.
   * @param array $params The parameters to pass to the partial template.
   * @return string The rendered content of the partial template.
   */
  public static function render(string $partialName, array $params = []): string
  {
    $templateName = "partials/{$partialName}";
    if (!isset(self::$cache[$templateName])) {
      self::$cache[$templateName] = self::templateNameToTemplatePath($templateName);
    }

    extract($params, EXTR_SKIP);
    ob_start();
    require self::$cache[$templateName];
    return ob_get_clean();
  }
}
