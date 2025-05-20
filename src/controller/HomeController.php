<?php

namespace App\Controller;

use AWSD\Controller\AbsctractController;

class HomeController extends AbsctractController
{
  
  function index(){
    $this->renderView("home/index", [
      "title" => "HomePage"
    ]);
  }

}
