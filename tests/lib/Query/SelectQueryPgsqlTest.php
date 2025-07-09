<?php

declare(strict_types=1);

use App\Model\User\UserEntity as User;
use PHPUnit\Framework\TestCase;
use AWSD\Database\Schema\Query\SelectQuery;


final class SelectQueryPgsqlTest extends TestCase
{
  protected function setUp(): void
  {
    \AWSD\Database\Schema\Config\ORMConfig::reset();
    $_ENV['DB_DRIVER'] = 'pgsql';
  }

  public function test_simple_select(): void
  {
    $query = new SelectQuery(User::class);
    $this->assertSame('SELECT * FROM "users" ;', $query->generateSql());
  }

  public function test_select_with_where_equals(): void
  {
    $query = new SelectQuery(User::class);
    $query->setWhere(['id' => 1]);

    $expectedSQL = 'SELECT * FROM "users" WHERE "id" = :id_1;';
    $expectedParams = [':id_1' => 1];
    $this->assertSame($expectedSQL, $query->generateSql());
    $this->assertSame($expectedParams, $query->getParams());
  }

  public function test_select_with_where_in(): void
  {
    $query = new SelectQuery(User::class);
    $query->setWhere(['status' => ['operator' => 'IN', 'value' => ['draft', 'published']]]);

    $expected = 'SELECT * FROM "users" WHERE "status" IN (:status_1, :status_2);';
    $this->assertSame($expected, $query->generateSql());
  }

  public function test_select_with_order_by(): void
  {
    $query = new SelectQuery(User::class);
    $query->setOrderBy(['created_at' => ['direction' => 'DESC', 'nulls' => 'LAST']]);
    $expected = 'SELECT * FROM "users" ORDER BY "created_at" DESC NULLS LAST;';
    $this->assertSame($expected, $query->generateSql());
  }

  public function test_select_with_limit_offset(): void
  {
    $query = new SelectQuery(User::class);
    $query->setLimit(10);
    $query->setOffset(20);

    $expectedSQL = 'SELECT * FROM "users" LIMIT :limit_1 OFFSET :offset_1;';
    $expectedParams = [':limit_1' => 10, ':offset_1' => 20];
    $this->assertSame($expectedSQL, $query->generateSql());
    $this->assertSame($expectedParams, $query->getParams());
  }

  public function test_select_with_inner_join(): void
  {
    $query = new SelectQuery(User::class);

    $query->setJoin([
      [
        'type' => 'INNER JOIN',
        'table' => 'posts',
        'on' => ['posts.user_id', '=', 'users.id'],
      ]
    ]);

    $expected = 'SELECT * FROM "users" INNER JOIN "posts" AS "posts_1" ON "posts_1"."user_id" = "users"."id";';

    $this->assertSame($expected, $query->generateSql());
  }

  public function test_select_with_multiple_joins(): void
  {
    $query = new SelectQuery(User::class);

    $query->setJoin([
      [
        'type' => 'LEFT JOIN',
        'table' => 'posts',
        'on' => ['posts.user_id', '=', 'users.id'],
      ],
      [
        'type' => 'INNER JOIN',
        'table' => 'categories',
        'on' => ['posts.category_id', '=', 'categories.id'],
      ],
    ]);

    $expected = 'SELECT * FROM "users" LEFT JOIN "posts" AS "posts_1" ON "posts_1"."user_id" = "users"."id" INNER JOIN "categories" AS "categories_1" ON "posts"."category_id" = "categories_1"."id";';

    $this->assertSame($expected, $query->generateSql());
  }

  public function test_select_with_group_by(): void
  {
    $query = new SelectQuery(User::class);
    $query->setFields(['status'])
      ->setExpression([['COUNT(*)' => 'total']])
      ->setGroupBy(["status"])
      ->setOrderBy(["total" => "DESC"]);
    $expected = 'SELECT "users"."status" AS "users_status", COUNT(*) AS "total" FROM "users" GROUP BY "status" ORDER BY "total" DESC;';
    $this->assertSame($expected, $query->generateSql());
  }

  public function test_multi_fields_group_by(): void
  {
    $query = new SelectQuery(User::class);
    $query->setFields(['status'])
      ->setExpression([['COUNT(*)' => 'total']])
      ->setGroupBy(["status", "name"])
      ->setOrderBy(["total" => "DESC"]);
    $expected = 'SELECT "users"."status" AS "users_status", COUNT(*) AS "total" FROM "users" GROUP BY "status", "name" ORDER BY "total" DESC;';
    $this->assertSame($expected, $query->generateSql());
  }

  public function test_empty_field_throws_exception(): void
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('GroupBy field must be a non-empty string');

    $query = new SelectQuery(User::class);
    $query->setFields(['status'])
      ->setGroupBy([""]);
  }


  public function test_potential_injection_not_sanitized(): void
  {
    $query = new SelectQuery(User::class);
    $query->setFields(['status'])
      ->setExpression([['COUNT(*)' => 'total']])
      ->setGroupBy(['user_id; DROP TABLE users;'])
      ->setOrderBy(["total" => "DESC"]);
    $expected = 'SELECT "users"."status" AS "users_status", COUNT(*) AS "total" FROM "users" GROUP BY "user_id; DROP TABLE users;" ORDER BY "total" DESC;';
    $this->assertSame($expected, $query->generateSql());
  }

  public function test_select_full_combo(): void
  {
    $query = new SelectQuery(User::class);
    $query->setWhere(['status' => 'published']);
    $query->setOrderBy(['created_at' => ['direction' => 'DESC', 'nulls' => 'LAST']]);
    $query->setLimit(10);

    $expectedSQL = 'SELECT * FROM "users" WHERE "status" = :status_1 ORDER BY "created_at" DESC NULLS LAST LIMIT :limit_1;';
    $expectedParams = [':status_1' => 'published', ':limit_1' => 10];
    $this->assertSame($expectedSQL, $query->generateSql());
    $this->assertSame($expectedParams, $query->getParams());
  }
}
