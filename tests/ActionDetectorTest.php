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
    $route = new RestRoute('Api');

    $url = new UrlScript();
    $url->setPath('/api/foo');
    $request = new Request($url, NULL, NULL, NULL, NULL,
      array(
        'accept' => 'application/json',
      ),
      $method
    );

    $appRequest = $route->match($request);

    $this->assertEquals('Api:Foo', $appRequest->getPresenterName());
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
}
