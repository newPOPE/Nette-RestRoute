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
      [
        'access_token' => 'foo-bar',
      ]
    );

    $request = new Request($url, NULL, NULL, NULL, NULL, NULL, 'GET');

    $appRequest = $route->match($request);

    $refUrl = new Url('http://localhost');
    $url = $route->constructUrl($appRequest, $refUrl);

    $expectedUrl = 'http://localhost/resource?access_token=foo-bar';
    $this->assertEquals($expectedUrl, $url);
  }

  public function testMatchAndConstructSpinalCaseUrlSingleResource() {
    $route = new RestRoute;

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
  }

  public function testMatchAndConstructSpinalCaseUrlMultipleResource() {
    $route = new RestRoute;

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

  public function testFileUpload() {
    $route = new RestRoute;

    $url = new UrlScript('http://localhost');
    $url->setPath('/whatever');
    $files = [ 'file1', 'file2', 'file3' ];

    $request = new Request($url, NULL, NULL, $files, NULL, NULL, 'POST');

    $appRequest = $route->match($request);
    $this->assertEquals($files, $appRequest->getFiles());
  }

  /**
   * @dataProvider getActions
   */
  public function testDefault($method, $path, $action, $id = null,  $associations = null) {
    $route = new RestRoute();

    $url = new UrlScript();
    $url->setPath($path);
    $request = new Request($url, NULL, NULL, NULL, NULL, NULL, $method);

    $appRequest = $route->match($request);

    $this->assertEquals('Foo', $appRequest->getPresenterName());
    $this->assertEquals($action, $appRequest->parameters['action']);

    if($id) {
      $this->assertEquals($id, $appRequest->parameters['id']);
    }
    if($associations) {
      $this->assertSame($associations, $appRequest->parameters['associations']);
    }
  }

  /**
   * @dataProvider getVersions
   */
  public function testModuleVersioning($module, $path, $expectedPresenterName, $expectedUrl) {
    $route = new RestRoute($module);
    $route->useURLModuleVersioning(
      '/v[0-9\.]+/', 
      [
        NULL => 'V1',
        'v1' => 'V1',
        'v2' => 'V2'
      ]
  );

    $url = new UrlScript();
    $url->setPath($path);
    $request = new Request($url, NULL, NULL, NULL, NULL, NULL, 'GET');

    $appRequest = $route->match($request);

    $this->assertEquals($expectedPresenterName, $appRequest->getPresenterName());

    $refUrl = new Url('http://localhost');
    $url = $route->constructUrl($appRequest, $refUrl);
    $this->assertEquals($expectedUrl, $url);
  }

  public function getActions() {
    return [
      ['POST', '/foo', 'create'],
      ['GET', '/foo', 'readAll'],
      ['GET', '/foo/1', 'read', 1],
      ['PATCH', '/foo', 'partialUpdate'],
      ['PUT', '/foo', 'update'],
      ['DELETE', '/foo', 'delete'],
      ['OPTIONS', '/foo', 'options'],
    ];
  }

  public function getVersions() {
    return [
      [NULL, '/foo', 'V1:Foo', 'http://localhost/v1/foo'],
      [NULL, '/v1/foo', 'V1:Foo', 'http://localhost/v1/foo'],
      [NULL, '/v2/foo', 'V2:Foo', 'http://localhost/v2/foo'],
      ['Api', '/api/foo', 'Api:V1:Foo', 'http://localhost/api/v1/foo'],
      ['Api', '/api/v1/foo', 'Api:V1:Foo', 'http://localhost/api/v1/foo'],
    ];
  }

}
