<?php

namespace AdamStipak;

use Nette\Http\UrlScript;

class ConstructUrlTest extends \PHPUnit_Framework_TestCase {

  public function testNoModuleNoAssociations() {
    $route = new RestRoute;
    $params = [
      RestRoute::KEY_PRESENTER => 'Resource',
      RestRoute::KEY_METHOD => \Nette\Http\Request::GET,
      'id' => 987
    ];

    $refUrl = new UrlScript('http://localhost/');
    $url = $route->constructUrl($params, $refUrl);

    $expectedUrl = 'http://localhost/resource/987';
    $this->assertEquals($expectedUrl, $url);
  }

  public function testWithModuleNoAssociations() {
    $route = new RestRoute('Api');
    $params = [
      RestRoute::KEY_PRESENTER => 'Api:Resource',
      RestRoute::KEY_METHOD => \Nette\Http\Request::GET,
      'id' => 987,
    ];

    $refUrl = new UrlScript('http://localhost/');
    $url = $route->constructUrl($params, $refUrl);

    $expectedUrl = 'http://localhost/api/resource/987';
    $this->assertEquals($expectedUrl, $url);
  }

  public function createAssociations() {
    return [
      [
        'associations' => [
          'foos' => 123,
        ],
        'result' => '/foos/123',
      ],
      [
        'associations' => [
          'foos' => 123,
          'bars' => 234,
        ],
        'result' => '/foos/123/bars/234',
      ],
      [
        'associations' => [
          'foos' => 123,
          'bars' => 234,
          'beers' => 345,
        ],
        'result' => '/foos/123/bars/234/beers/345',
      ],
      [
        'associations' => [
          'foos-bars' => 123,
        ],
        'result' => '/foos-bars/123',
      ],
    ];
  }

  /**
   * @dataProvider createAssociations
   */
  public function testWithModuleAndAssociations($associations, $result) {
    $route = new RestRoute('Api');
    $params = [
      RestRoute::KEY_PRESENTER => 'Api:Resource',
      RestRoute::KEY_METHOD => \Nette\Http\Request::GET,
      'id' => 987,
      RestRoute::KEY_ASSOCIATIONS => $associations
    ];

    $refUrl = new UrlScript('http://localhost/');
    $url = $route->constructUrl($params, $refUrl);

    $expectedUrl = "http://localhost/api{$result}/resource/987";
    $this->assertEquals($expectedUrl, $url);
  }

  public function testDefaultsWithBasePath() {
    $route = new RestRoute;
    $params = [
      RestRoute::KEY_PRESENTER => 'Resource',
      RestRoute::KEY_METHOD => \Nette\Http\Request::GET,
      'id' => 987,
    ];

    $refUrl = (new UrlScript('http://localhost/base-path/'));
    $url = $route->constructUrl($params, $refUrl);

    $expectedUrl = 'http://localhost/base-path/resource/987';
    $this->assertEquals($expectedUrl, $url);
  }

  public function testUrlOnSubdomain() {
    $route = new RestRoute;
    $params = [
      RestRoute::KEY_PRESENTER => 'Resource',
      RestRoute::KEY_METHOD => \Nette\Http\Request::GET,
      'id' => 987,
    ];

    $refUrl = new UrlScript('http://api.foo.bar');
    $url = $route->constructUrl($params, $refUrl);

    $expectedUrl = 'http://api.foo.bar/resource/987';
    $this->assertEquals($expectedUrl, $url);
  }

  public function testQueryParams() {
    $route = new RestRoute;
    $params = [
      RestRoute::KEY_PRESENTER => 'Resource',
      RestRoute::KEY_METHOD => \Nette\Http\Request::GET,
      'id' => 987,
      'query' => [
        'foo' => 'bar',
        'baz' => 'bay',
      ],
    ];

    $refUrl = new UrlScript('http://api.foo.bar');
    $url = $route->constructUrl($params, $refUrl);

    $expectedUrl = 'http://api.foo.bar/resource/987?foo=bar&baz=bay';
    $this->assertEquals($expectedUrl, $url);
  }
}
