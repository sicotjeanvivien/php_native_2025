<?php

namespace AWSD\Template;

use AWSD\Exception\HttpException;

/**
 * Class Section
 *
 * This class provides methods to manage sections of content, allowing for buffering and retrieval of section content.
 */
class Section
{
  /**
   * @var array Stores the sections content.
   */
  private static array $sections = [];

  /**
   * @var string|null The current section being processed.
   */
  private static ?string $currentSection = null;

  /**
   * Starts a new section or sets the content of a section.
   *
   * This method allows you to start a new section for buffering or directly set the content of a section.
   *
   * @param string $name The name of the section.
   * @param string|null $content The content of the section. If null, starts buffering.
   * @throws HttpException If a section is already open.
   */
  public static function startSection(string $name, ?string $content = null): void
  {
    if ($content !== null) {
      self::$sections[$name] = $content;
    } else {
      if (self::$currentSection !== null) {
        throw new HttpException("Cannot start section '$name': section '" . self::$currentSection . "' is already open.", 404);
      }
      self::$currentSection = $name;
      ob_start();
    }
  }

  /**
   * Ends the current section and stores its content.
   *
   * This method ends the current section and stores its buffered content.
   *
   * @throws HttpException If no section is currently open.
   */
  public static function endSection(): void
  {
    if (self::$currentSection === null) {
      throw new HttpException("No section is currently open.", 404);
    }

    self::$sections[self::$currentSection] = ob_get_clean();
    self::$currentSection = null;
  }

  /**
   * Retrieves the content of a section.
   *
   * This method retrieves the content of a section by its name.
   *
   * @param string $name The name of the section.
   * @param string $default The default content to return if the section does not exist.
   * @return string The content of the section, or the default content if the section does not exist.
   */
  public static function yield(string $name, string $default = ""): string
  {
    return self::$sections[$name] ?? $default;
  }

  /**
   * Checks if a section exists.
   *
   * This method checks if a section exists by its name.
   *
   * @param string $name The name of the section.
   * @return bool True if the section exists, false otherwise.
   */
  public static function hasSection(string $name): bool
  {
    return isset(self::$sections[$name]);
  }

  /**
   * Clears all sections.
   *
   * This method clears all sections and resets the current section.
   */
  public static function clearSections(): void
  {
    self::$sections = [];
    self::$currentSection = null;
  }
}
