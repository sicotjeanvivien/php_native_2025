<?php

namespace App\Controller;

use AWSD\Controller\AbstractController;

class UserController extends AbstractController
{

  public function login(): void
  {
    $this->renderView("user/login", [
      "title" => "LOginPage"
    ]);
  }
}
