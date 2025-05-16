<?php

use PHPUnit\Framework\TestCase;
use AWSD\Autoloader;
use AWSD\Exception\AutoloadException;

class AutoloaderTest extends TestCase
{
  private $autoloader;
  private $aliases;
  private $rootPath;
  private $fileExtension;

  protected function setUp(): void
  {
    $this->aliases = ["App" => "src", "AWSD" => "lib"];
    $this->rootPath = __DIR__;
    $this->fileExtension = ".php";

    $this->autoloader = new Autoloader($this->aliases, $this->rootPath, $this->fileExtension);
  }

  public function testConstructor()
  {
    $this->assertInstanceOf(Autoloader::class, $this->autoloader);
  }

  public function testRegister()
  {
    $this->autoloader->register();
    $this->assertTrue(in_array([$this->autoloader, 'loadClass'], spl_autoload_functions()));
  }

  public function testNamespaceToFilepath()
  {
    $class = "App\\Example\\TestClass";
    $expectedFilepath = $this->rootPath . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Example" . DIRECTORY_SEPARATOR . "TestClass" . $this->fileExtension;

    $reflection = new ReflectionClass($this->autoloader);
    $method = $reflection->getMethod('namespaceToFilepath');
    $method->setAccessible(true);

    $filepath = $method->invoke($this->autoloader, $class);
    $this->assertEquals($expectedFilepath, $filepath);
  }

  public function testNamespaceToFilepathWithInvalidNamespace()
  {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("Namespace « InvalidNamespace » is invalid. Allowed: App, AWSD");

    $class = "InvalidNamespace\\Example\\TestClass";

    $reflection = new ReflectionClass($this->autoloader);
    $method = $reflection->getMethod('namespaceToFilepath');
    $method->setAccessible(true);

    $method->invoke($this->autoloader, $class);
  }

  public function testLoadClass()
  {
    $class = "AWSD\\Utils\\DummyClass";
    $filepath = $this->rootPath . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . "Utils" . DIRECTORY_SEPARATOR . "DummyClass" . $this->fileExtension;

    // Create a dummy file for testing
    if (!file_exists(dirname($filepath))) {
      mkdir(dirname($filepath), 0777, true);
    }
    file_put_contents($filepath, "<?php namespace AWSD\Utils; class DummyClass{public function show(): void{echo 'controller called';}}");

    $reflection = new ReflectionClass($this->autoloader);
    $method = $reflection->getMethod('loadClass');
    $method->setAccessible(true);

    $method->invoke($this->autoloader, $class);
    $this->assertTrue(class_exists("AWSD\\Utils\\DummyClass"));

    // Clean up
    unlink($filepath);
    rmdir(dirname($filepath));
  }

  public function testLoadClassWithInvalidFile(): void
  {
    $this->expectException(AutoloadException::class);
    $this->expectExceptionMessage("File « " . $this->rootPath . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Example" . DIRECTORY_SEPARATOR . "TestClass" . $this->fileExtension . " » not found for class « App\\Example\\TestClass ».");

    $class = "App\\Example\\TestClass";

    $reflection = new ReflectionClass($this->autoloader);
    $method = $reflection->getMethod('loadClass');
    $method->setAccessible(true);

    $method->invoke($this->autoloader, $class);
  }
}
