<?php

namespace AWSD\Schema\Mapper\SGBD;

interface TriggerMapperInterface
{
    /**
     * Returns the name of the function used for the trigger.
     *
     * @param string $tableName
     * @param string $column
     * @return string
     */
    public function getFunctionName(string $tableName, string $column): string;

    /**
     * Returns the body of the trigger function (only the SQL body).
     *
     * @param string $column
     * @return string
     */
    public function getFunctionBody(string $column): string;

    /**
     * Returns the complete trigger declaration (CREATE TRIGGER ... EXECUTE ...).
     *
     * @param string $tableName
     * @param string $functionName
     * @return string
     */
    public function getTriggerDeclaration(string $tableName, string $functionName): string;

    /**
     * Indicates whether this SGBD supports triggers.
     *
     * @return bool
     */
    public function supportsTriggers(): bool;
}
