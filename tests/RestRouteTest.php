<?php

use AdamStipak\RestRoute;

class RestRouteTest extends PHPUnit_Framework_TestCase {

  public function testConstructorWithNoModule(){
    $route = new RestRoute;
  }

  public function testConstructorWithEmptyDefaultFormat() {
    $route = new RestRoute('Api');
  }

  /**
   * @expectedException \Nette\InvalidArgumentException
   */
  public function testConstructorWithInvalidDefaultFormat() {
    $route = new RestRoute('Api', 'invalid');
  }

  public function testConstructorWithXmlAsADefaultFormat(){
    $route = new RestRoute('Api', 'xml');

    $defaultFormat = $route->getDefaultFormat();
    $this->assertEquals('xml', $defaultFormat);
  }
}
