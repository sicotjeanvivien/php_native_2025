<?php

namespace AWSD\View;

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
     * @param string $name The name of the section.
     * @param string|null $content The content of the section. If null, starts buffering.
     */
    public static function startSection(string $name, ?string $content = null): void
    {
        if ($content !== null) {
            self::$sections[$name] = $content;
        } else {
            if (self::$currentSection !== null) {
                throw new \RuntimeException("Cannot start section '$name': section '" . self::$currentSection . "' is already open.");
            }
            self::$currentSection = $name;
            ob_start();
        }
    }

    /**
     * Ends the current section and stores its content.
     */
    public static function endsection(): void
    {
        if (self::$currentSection === null) {
            throw new \RuntimeException("No section is currently open.");
        }

        self::$sections[self::$currentSection] = ob_get_clean();
        self::$currentSection = null;
    }

    /**
     * Retrieves the content of a section.
     *
     * @param string $name The name of the section.
     * @return string The content of the section, or an empty string if the section does not exist.
     */
    public static function yield(string $name, string $default= ""): string
    {
        return self::$sections[$name] ?? $default;
    }

    /**
     * Checks if a section exists.
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
     */
    public static function clearSections(): void
    {
        self::$sections = [];
        self::$currentSection = null;
    }
}
