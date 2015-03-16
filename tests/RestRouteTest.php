<?php

use AdamStipak\RestRoute;
use Nette\Http\UrlScript;
use Nette\Http\Request;
use Nette\Http\Url;

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

  public function testMatchAndConstructUrl() {
    $route = new RestRoute;

    $url = new UrlScript('http://localhost');
    $url->setPath('/resource');
    $url->setQuery(
      array(
        'access_token' => 'foo-bar',
      )
    );

    $request = new Request($url, NULL, NULL, NULL, NULL, NULL, 'GET');

    $appRequest = $route->match($request);

    $refUrl = new Url('http://localhost');
    $url = $route->constructUrl($appRequest, $refUrl);

    $expectedUrl = 'http://localhost/resource?access_token=foo-bar';
    $this->assertEquals($expectedUrl, $url);
  }
}
