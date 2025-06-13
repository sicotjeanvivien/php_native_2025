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
    $entityBuilder = new EntitySchemaBuilder((new Migration()));
    $array = $entityBuilder->findAll([]);
    $queryExecutor =  new QueryExecutor(Migration::class);
    $resp = $queryExecutor->executeQuery("SELECT * FROM migrations;");
    var_dump($array);
    var_dump($resp);
    // $user = new UserEntity();
    // $selectQuery = new SelectQuery($user);
    // $selectQuery->setWhere([
    //   'email' => ['operator' => 'like', 'value' => '%john%'],
    //   'createdAt' => ['operator' => 'between', 'value' => ['2024-01-01', '2024-12-31']]
    // ]);

    // var_dump($selectQuery->generateSql());
    // var_dump($selectQuery->getParams());
  }
}
