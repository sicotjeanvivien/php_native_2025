<?php

use PHPUnit\Framework\TestCase;
use AWSD\Utils\DummyClass;

final class AutoloaderTest extends TestCase
{
  public function testAdd(): void
  {
    $testClass = new DummyClass();
    $this->assertEquals("Hello", $testClass->sayHello());
  }
}
