<?php

namespace AWSD\Script;

use App\Model\User\UserEntity;
use AWSD\Database\QueryExecutor;
use AWSD\Schema\Migration\Migration;
use AWSD\Schema\EntitySchemaBuilder;
use AWSD\Schema\Query\SelectQuery;

final class TestQueryScript extends AbstractScript
{
  public function run(): void
  {
    $user = new UserEntity();
    $selectQuery = new SelectQuery(UserEntity::class);
    $selectQuery->setWhere([
      'email' => ['operator' => 'like', 'value' => '%john%'],
      'created_at' => ['operator' => 'between', 'value' => ['2024-01-01', '2024-12-31']]
    ]);
    $selectQuery->setLimit(100)->setOffset(10);
    $selectQuery->setOrderBy([
      'name' => 'ASC',
      'created_at' => ['direction' => 'DESC', 'nulls' => 'LAST']
    ]);
    $selectQuery->setJoin(
      [
        'table' => 'posts',
        'on' => ['posts.user_id', '=', 'user.id'],
        'type' => 'LEFT JOIN'
      ]
    );
    $selectQuery->setDistinct()->setFields(['id', 'name']);

    var_dump($selectQuery->generateSql());
    var_dump($selectQuery->getParams());
  }
}
