<?php

namespace AdamStipak;

use Nette\Http\UrlScript;
use Nette\Http\Request;

class ActionDetectorTest extends \PHPUnit\Framework\TestCase {

  /**
   * @param $method
   * @param $action
   *
   * @dataProvider getActions
   */
  public function testAction($method, $action) {
    $route = new RestRoute();

    $url = (new UrlScript())->withPath('/foo');
    $request = new Request($url, [], [], [], [], $method);

    $parameters = $route->match($request);

    $this->assertEquals('Foo', $parameters[RestRoute::KEY_PRESENTER]);
    $this->assertEquals($action, $parameters[RestRoute::KEY_ACTION]);
  }

  public static function getActions() {
    return [
      ['POST', 'create'],
      ['GET', 'readAll'],
      ['PATCH', 'partialUpdate'],
      ['PUT', 'update'],
      ['DELETE', 'delete'],
      ['OPTIONS', 'options'],
    ];
  }

  public static function getActionsForOverride() {
    return [
      ['PATCH', 'partialUpdate'],
      ['PUT', 'update'],
      ['DELETE', 'delete'],
    ];
  }
}
