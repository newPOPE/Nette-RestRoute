<?php

namespace AdamStipak;

use Nette\Http\UrlScript;
use Nette\Http\Request;

class ActionDetectorTest extends \PHPUnit_Framework_TestCase {

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
    return [
      ['POST', 'create'],
      ['GET', 'readAll'],
      ['PATCH', 'partialUpdate'],
      ['PUT', 'update'],
      ['DELETE', 'delete'],
      ['OPTIONS', 'options'],
    ];
  }

  public function getActionsForOverride() {
    return [
      ['PATCH', 'partialUpdate'],
      ['PUT', 'update'],
      ['DELETE', 'delete'],
    ];
  }
}
