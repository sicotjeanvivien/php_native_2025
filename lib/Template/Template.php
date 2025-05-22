<?php

namespace AWSD\Template;

use AWSD\Exception\HttpException;

/**
 * Class Template
 *
 * This class provides methods to handle template paths and conversions.
 */
class Template
{
  /**
   * The directory where templates are stored.
   */
  private const TEMPLATE_DIR = ROOT_PATH . '/templates';

  /**
   * Converts a template name to its full path.
   *
   * This method takes a template name and converts it to the full path by appending
   * the template directory and the .phtml extension.
   *
   * @param string $templateName The name of the template.
   * @return string The full path to the template.
   * @throws HttpException If the template is not found.
   */
  protected static function templateNameToTemplatePath(string $templateName): string
  {
    $templatePath = self::TEMPLATE_DIR . "/{$templateName}.phtml";

    if (!file_exists($templatePath)) {
      throw new HttpException("Template not found: {$templatePath}", 404);
    }

    return $templatePath;
  }
}
