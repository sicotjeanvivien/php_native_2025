<?php

namespace App\Controller;

use AWSD\Controller\AbsctractController;

class HomeController extends AbsctractController
{

  function index()
  {
    $this->renderView("home/index", [
      "title" => "HomePage",
      "DB_HOST" => $_ENV["DB_HOST"],
      "DB_DATABASE" => $_ENV["DB_DATABASE"],
      "DB_USERNAME" => $_ENV["DB_USERNAME"],
      "DB_PASSWORD" => $_ENV["DB_PASSWORD"]
    ]);
  }
}
