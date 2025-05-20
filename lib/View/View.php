<?php

namespace AWSD\View;

use AWSD\Exception\HttpException;

/**
 * Class View
 *
 * This class is responsible for rendering templates and handling view-related tasks.
 */
class View
{

  /**
   * The directory where templates are stored.
   */
  private const TEMPLATE_DIR = ROOT_PATH . '/templates';

  /**
   * Renders a template with the given parameters.
   *
   * @param string $templateName The name of the template to render.
   * @param array $params The parameters to pass to the template.
   * @throws HttpException If the template is not found.
   */
  public static function render(string $templateName, array $params = []): void
  {
    $templatePath = self::templateNameToTemplatePath($templateName);
    self::generateView($templatePath, $params);
  }

  /**
   * Renders an error template with the given status code and message.
   *
   * @param int $statusCode The HTTP status code.
   * @param string $message The error message to display.
   */
  public static function renderError(int $statusCode, string $message = ''): void
  {
    http_response_code($statusCode);
    $templateName = "errors/{$statusCode}";

    try {
      $templatePath = self::templateNameToTemplatePath($templateName);
      $params = [
        "title" => "$statusCode - Error",
        "message" => $message
      ];
      self::generateView($templatePath, $params, "errors/layout");
    } catch (HttpException $e) {
      error_log($e->getMessage());
      echo "<h1>$statusCode</h1><p>$message</p>";
    }
  }

  /**
   * Converts a template name to its full path.
   *
   * @param string $templateName The name of the template.
   * @return string The full path to the template.
   * @throws HttpException If the template is not found.
   */
  private static function templateNameToTemplatePath(string $templateName): string
  {
    $templatePath = self::TEMPLATE_DIR . "/templates/{$templateName}.phtml";

    if (!file_exists($templatePath)) {
      throw new HttpException("Template not found", 404);
    }

    return $templatePath;
  }

  /**
   * Generates a view by including the template and layout.
   *
   * @param string $templatePath The path to the template.
   * @param array $params The parameters to pass to the template.
   * @param string $layout The layout to use.
   * @throws HttpException If the layout template is not found.
   */
  private static function generateView(string $templatePath, array $params, string $layout = "layout"): void
  {
    extract($params, EXTR_SKIP);
    ob_start();
    require $templatePath;
    $content = ob_get_clean();
    require self::templateNameToTemplatePath($layout);
  }
}
