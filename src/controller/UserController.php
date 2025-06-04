<?php

namespace App\Controller;

use AWSD\Controller\AbsctractController;

class UserController extends AbsctractController
{

  public function login(): void
  {
    $this->renderView("user/login", [
      "title" => "LOginPage"
    ]);
  }
}
