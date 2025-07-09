<?php

namespace App\Controller;

use AWSD\Controller\AbstractController;

class ArticleController extends AbstractController
{
  public function show($id): void
  {
    $this->renderView("article/show", [
      "title" => "Article Page",
      "article_id" => $id
    ]);
  }
}
