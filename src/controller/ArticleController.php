<?php

namespace App\Controller;

use AWSD\Controller\AbsctractController;

class ArticleController extends AbsctractController
{
  public function show($id): void
  {
    $this->renderView("article/show", [
      "title" => "Article Page",
      "article_id" => $id
    ]);
  }
}
