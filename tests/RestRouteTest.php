<?php

namespace AdamStipak;

use Nette\Http\UrlScript;
use Nette\Http\Request;
use Nette\Http\Url;

class RestRouteTest extends \PHPUnit_Framework_TestCase {

  public function testConstructorWithNoModule() {
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

  public function testConstructorWithXmlAsADefaultFormat() {
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

  public function testMatchAndConstructSpinalCaseUrl() {
    $route = new RestRoute;

    // Single resource
    $url = new UrlScript('http://localhost');
    $url->setPath('/re-source');

    $request = new Request($url, NULL, NULL, NULL, NULL, NULL, 'GET');

    $appRequest = $route->match($request);
    $expectedPresenterName = 'ReSource';
    $this->assertEquals($expectedPresenterName, $appRequest->getPresenterName());

    $refUrl = new Url('http://localhost');
    $url = $route->constructUrl($appRequest, $refUrl);

    $expectedUrl = 'http://localhost/re-source';
    $this->assertEquals($expectedUrl, $url);

    // Multiple level resource
    $url = new UrlScript('http://localhost');
    $url->setPath('/first-level/123/second-level/456/re-source');

    $request = new Request($url, NULL, NULL, NULL, NULL, NULL, 'GET');

    $appRequest = $route->match($request);
    $expectedPresenterName = 'ReSource';
    $this->assertEquals($expectedPresenterName, $appRequest->getPresenterName());

    $refUrl = new Url('http://localhost');
    $url = $route->constructUrl($appRequest, $refUrl);

    $expectedUrl = 'http://localhost/first-level/123/second-level/456/re-source';
    $this->assertEquals($expectedUrl, $url);
  }
}
