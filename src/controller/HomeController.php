<?php

namespace App\Controller;

use AWSD\Controller\AbsctractController;
use AWSD\Database\Database;

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
