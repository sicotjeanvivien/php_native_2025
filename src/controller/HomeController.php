<?php

namespace App\Controller;

use AWSD\Controller\AbsctractController;
use AWSD\Utils\Database;

class HomeController extends AbsctractController
{

  function index():void
  {
    $pdo = Database::getInstance();
    $pdo->errorCode();
    $this->renderView("home/index", [
      "title" => "HomePage"
    ]);
  }
}
