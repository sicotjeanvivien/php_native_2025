<?php

namespace AWSD\Controller;

use AWSD\View\View;

class AbsctractController
{
  public function renderView(string $templateName, array $params = []): mixed
  {
    return View::render($templateName, $params);
  }
}
