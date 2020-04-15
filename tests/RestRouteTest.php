<?php

namespace AdamStipak;

use Nette\Http\UrlScript;
use Nette\Http\Request;

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

    $url = (new UrlScript('http://localhost'))
      ->withPath('/resource')
      ->withQuery(['access_token' => 'foo-bar']);

    $request = new Request($url, null, null, null, null, 'GET');

    $appRequest = $route->match($request);

    $refUrl = new UrlScript('http://localhost');
    $url = $route->constructUrl($appRequest, $refUrl);

    $expectedUrl = 'http://localhost/resource?access_token=foo-bar';
    $this->assertEquals($expectedUrl, $url);
  }

  public function testMatchAndConstructSpinalCaseUrlSingleResource() {
    $route = new RestRoute;

    $url = (new UrlScript('http://localhost'))->withPath('/re-source');

    $request = new Request($url, null, null, null, null, 'GET');

    $params = $route->match($request);
    $expectedPresenterName = 'ReSource';
    $this->assertEquals($expectedPresenterName, $params[RestRoute::KEY_PRESENTER]);

    $refUrl = new UrlScript('http://localhost');
    $url = $route->constructUrl($params, $refUrl);

    $expectedUrl = 'http://localhost/re-source';
    $this->assertEquals($expectedUrl, $url);
  }

  public function testMatchAndConstructSpinalCaseUrlMultipleResource() {
    $route = new RestRoute;

    $url = (new UrlScript('http://localhost'))->withPath('/first-level/123/second-level/456/re-source', '/');

    $request = new Request($url, null, null, null, null, 'GET');

    $params = $route->match($request);
    $expectedPresenterName = 'ReSource';
    $this->assertEquals($expectedPresenterName, $params[RestRoute::KEY_PRESENTER]);

    $refUrl = new UrlScript('http://localhost');
    $url = $route->constructUrl($params, $refUrl);

    $expectedUrl = 'http://localhost/first-level/123/second-level/456/re-source';
    $this->assertEquals($expectedUrl, $url);
  }

  public function testFileUpload() {
    $route = new RestRoute;

    $url = (new UrlScript('http://localhost'))->withPath('/whatever');
    $files = ['file1', 'file2', 'file3'];

    $request = new Request($url, null, $files, null, null, 'POST');
    $params = $route->match($request);

    $this->assertEquals($files, $params[RestRoute::KEY_FILES]);
  }

  /**
   * @dataProvider getActions
   */
  public function testDefault($method, $path, $action, $id = null, $associations = null) {
    $route = new RestRoute();

    $url = (new UrlScript())->withPath($path, '/');
    $request = new Request($url, null, null, null, null, $method);

    $params = $route->match($request);

    $this->assertEquals('Foo', $params[RestRoute::KEY_PRESENTER]);
    $this->assertEquals($action, $params[RestRoute::KEY_ACTION]);

    if ($id) {
      $this->assertEquals($id, $params['id']);
    }
    if ($associations) {
      $this->assertSame($associations, $params[RestRoute::KEY_ASSOCIATIONS]);
    }
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

  /**
   * @dataProvider getVersions
   */
  public function testModuleVersioning($module, $path, $expectedPresenterName, $expectedUrl) {
    $route = new RestRoute($module);
    $route->useURLModuleVersioning(
      RestRoute::MODULE_VERSION_PATH_PREFIX_PATTERN,
      [
        null => 'V1',
        'v1' => 'V1',
        'v2' => 'V2'
      ]
    );

    $url = (new UrlScript())->withPath($path, '/');
    $request = new Request($url, null, null, null, null, 'GET');

    $params = $route->match($request);

    $this->assertEquals($expectedPresenterName, $params[RestRoute::KEY_PRESENTER]);

    $refUrl = new UrlScript('http://localhost');
    $url = $route->constructUrl($params, $refUrl);
    $this->assertEquals($expectedUrl, $url);
  }

  public function getVersions() {
    return [
      [null, '/foo', 'V1:Foo', 'http://localhost/v1/foo'],
      [null, '/v1/foo', 'V1:Foo', 'http://localhost/v1/foo'],
      [null, '/v2/foo', 'V2:Foo', 'http://localhost/v2/foo'],
      ['Api', '/api/foo', 'Api:V1:Foo', 'http://localhost/api/v1/foo'],
      ['Api', '/api/v1/foo', 'Api:V1:Foo', 'http://localhost/api/v1/foo'],
    ];
  }

}
