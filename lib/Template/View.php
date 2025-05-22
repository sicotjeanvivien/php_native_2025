<?php

namespace AWSD\Template;

use AWSD\Exception\HttpException;

/**
 * Class View
 *
 * This class is responsible for rendering templates and handling view-related tasks.
 */
class View extends Template
{
	/**
	 * Renders a template with the given parameters.
	 *
	 * This method takes a template name and an array of parameters, then renders the template.
	 *
	 * @param string $templateName The name of the template to render.
	 * @param array $params The parameters to pass to the template.
	 * @throws HttpException If the template is not found.
	 */
	public static function render(string $templateName, array $params = []): void
	{
		require self::generateView($templateName, $params);
	}

	/**
	 * Returns a template with the given parameters.
	 *
	 * This method takes a template name and an array of parameters, then returns the rendered template as a string.
	 *
	 * @param string $templateName The name of the template to render.
	 * @param array $params The parameters to pass to the template.
	 * @return string The rendered template as a string.
	 * @throws HttpException If the template is not found.
	 */
	public static function make(string $templateName, array $params = []): string
	{
		return self::generateView($templateName, $params);
	}

	/**
	 * Renders an error template with the given status code and message.
	 *
	 * This method sets the HTTP response code and renders an error template with the given status code and message.
	 *
	 * @param int $statusCode The HTTP status code.
	 * @param string $message The error message to display.
	 */
	public static function renderError(int $statusCode, string $message = ''): void
	{
		http_response_code($statusCode);
		$templateName = "errors/{$statusCode}";
		try {
			$params = [
				"title" => "$statusCode - Error",
				"message" => $message
			];
			echo self::generateView($templateName, $params, "errors/layout");
		} catch (HttpException $e) {
			error_log($e->getMessage());
			echo "<h1>$statusCode</h1><p>$message</p>";
		}
	}

	/**
	 * Generates a view by including the template and layout.
	 *
	 * This method generates a view by including the template and layout, and returns the path to the layout.
	 *
	 * @param string $templateName The name of the template.
	 * @param array $params The parameters to pass to the template.
	 * @param string $layout The layout to use.
	 * @return string The path to the layout.
	 * @throws HttpException If the layout template is not found.
	 */
	private static function generateView(string $templateName, array $params, string $layout = "layout"): string
	{
		Section::clearSections();
		extract($params, EXTR_SKIP);

		ob_start();
		require self::templateNameToTemplatePath($templateName);
		ob_end_clean();
		return self::templateNameToTemplatePath($layout);
	}
}
