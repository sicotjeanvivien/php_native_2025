<?php

namespace App\Controller;

use AWSD\Controller\AbstractController;
use AWSD\Database\Database;

class HomeController extends AbstractController
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
