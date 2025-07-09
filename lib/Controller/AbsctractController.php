<?php

namespace AWSD\Controller;

use AWSD\Template\View;

abstract class AbstractController
{
  public function renderView(string $templateName, array $params = []): mixed
  {
    return View::render($templateName, $params);
  }

  public  static function makeView(string $templateName, array $params = []): mixed
  {
    return View::make($templateName, $params);
  }
}
