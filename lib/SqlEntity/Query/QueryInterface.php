<?php

namespace AWSD\SqlEntity\Query;

interface QueryInterface
{
    /**
     * Accepts an entity object for SQL generation.
     *
     * @param object $entity The entity from which SQL should be generated.
     */
    public function __construct(object $entity);

    public function generateSql(): string;
}

