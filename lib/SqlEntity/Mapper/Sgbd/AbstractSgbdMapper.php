<?php

namespace AWSD\SqlEntity\Mapper\Sgbd;

use AWSD\SqlEntity\Attribute\Type;
use AWSD\SqlEntity\Enum\TypeEnum;

abstract class AbstractSgbdMapper implements SgbdMapperInterface
{

  protected readonly ?Type $metadata;
  protected readonly TypeEnum $typeSql;

  public function __construct(?Type $metadata, TypeEnum $typeSql)
  {
    $this->metadata = $metadata;
    $this->typeSql = $typeSql;
  }

  protected function quoteDefault(mixed $value): string
  {

    if (is_string($value) && strtoupper(trim($value)) === 'CURRENT_TIMESTAMP') {
      return 'CURRENT_TIMESTAMP';
    }

    return match (gettype($value)) {
      'string' => "'" . addslashes($value) . "'",
      'bool'   => $value ? 'TRUE' : 'FALSE',
      default  => (string) $value,
    };
  }
}
