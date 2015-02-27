<?php

use AdamStipak\RestRoute;
use Nette\Http\UrlScript;
use Nette\Http\Request;

class ActionDetectorTest extends PHPUnit_Framework_TestCase {

  /**
   * @param $method
   * @param $action
   *
   * @dataProvider getActions
   */
  public function testAction($method, $action) {
    $route = new RestRoute();

    $url = new UrlScript();
    $url->setPath('/foo');
    $request = new Request($url, NULL, NULL, NULL, NULL, NULL, $method);

    $appRequest = $route->match($request);

    $this->assertEquals('Foo', $appRequest->getPresenterName());
    $this->assertEquals($action, $appRequest->parameters['action']);
  }

  public function getActions() {
    return array(
      array('POST', 'create'),
      array('GET', 'read'),
      array('PUT', 'update'),
      array('DELETE', 'delete'),
    );
  }

  public function getActionsForOverride() {
    return array(
      array('PUT', 'update'),
      array('DELETE', 'delete'),
    );
  }

  /**
   * @dataProvider getActionsForOverride
   */
  public function testOverrideMethodViaHttpHeader($method, $action) {
    $route = new RestRoute('Api');

    $url = new UrlScript();
    $url->setPath('/api/foo');
    $request = new Request($url, NULL, NULL, NULL, NULL,
      array(
        'x-http-method-override' => $method,
      ),
      'POST'
    );

    $appRequest = $route->match($request);

    $this->assertEquals($action, $appRequest->parameters['action']);
  }

  /**
   * @dataProvider getActionsForOverride
   */
  public function testOverrideMethodViaQueryParameter($method, $action) {
    $route = new RestRoute('Api');

    $url = new UrlScript();
    $url->setPath('/api/foo');
    $url->setQuery(
      array(
        '__method' => $method,
      )
    );
    $request = new Request(
      $url,
      NULL, NULL, NULL, NULL, NULL, 'POST'
    );

    $appRequest = $route->match($request);

    $this->assertEquals($action, $appRequest->parameters['action']);
  }

  /**
   * @expectedException \Nette\InvalidStateException
   */
  public function testOverrideMethodWithInvalidMethod() {
    $method = 'invalid';
    $route = new RestRoute('Api');

    $url = new UrlScript();
    $url->setPath('/api/foo');
    $url->setQuery(
      array(
        '__method' => $method,
      )
    );
    $request = new Request(
      $url,
      NULL, NULL, NULL, NULL, NULL, 'POST'
    );

    $appRequest = $route->match($request);
  }

  public function testReadAllActionInsteadOfRead() {
    $route = new RestRoute('Api');
    $route->useReadAll();

    $url = new UrlScript();
    $url->setPath('/api/foo');
    $request = new Request(
      $url, NULL, NULL, NULL, NULL, NULL, 'GET'
    );

    $appRequest = $route->match($request);

    $this->assertEquals('readAll', $appRequest->parameters['action']);
  }
}
