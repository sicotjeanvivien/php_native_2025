<?php

namespace App\Controller;

use AWSD\Controller\AbsctractController;
use AWSD\Utils\Database;

class HomeController extends AbsctractController
{

  function index()
  {

    $database = new Database();
    $conn = $database->connect();
    var_dump($conn);
    $this->renderView("home/index", [
      "title" => "HomePage"
    ]);
  }
}
