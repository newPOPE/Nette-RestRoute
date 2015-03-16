<?php

use AdamStipak\RestRoute;
use Nette\Application\Request;
use Nette\Http\Url;

class ConstructUrlTest extends \PHPUnit_Framework_TestCase {

  public function testNoModuleNoAssociations() {
    $route = new RestRoute;

    $appRequest = new Request(
      'Resource',
      \Nette\Http\Request::GET,
      array(
        'id' => 987
      )
    );

    $refUrl = new Url('http://localhost/');

    $url = $route->constructUrl($appRequest, $refUrl);

    $expectedUrl = 'http://localhost/resource/987';
    $this->assertEquals($expectedUrl, $url);
  }

  public function testWithModuleNoAssociations() {
    $route = new RestRoute('Api');

    $appRequest = new Request(
      'Api:Resource',
      \Nette\Http\Request::GET,
      array(
        'id' => 987
      )
    );

    $refUrl = new Url('http://localhost/');

    $url = $route->constructUrl($appRequest, $refUrl);

    $expectedUrl = 'http://localhost/api/resource/987';
    $this->assertEquals($expectedUrl, $url);
  }

  public function createAssociations() {
    return array(
      array(
        'associations' => array(
          'foos' => 123,
        ),
        'result' => '/foos/123',
      ),
      array(
        'associations' => array(
          'foos' => 123,
          'bars' => 234,
        ),
        'result' => '/foos/123/bars/234',
      ),
      array(
        'associations' => array(
          'foos'  => 123,
          'bars'  => 234,
          'beers' => 345,
        ),
        'result' => '/foos/123/bars/234/beers/345',
      ),
    );
  }

  /**
   * @dataProvider createAssociations
   */
  public function testWithModuleAndAssociations($associations, $result) {
    $route = new RestRoute('Api');

    $appRequest = new Request(
      'Api:Resource',
      \Nette\Http\Request::GET,
      array(
        'id'           => 987,
        'associations' => $associations,
      )
    );

    $refUrl = new Url('http://localhost/');

    $url = $route->constructUrl($appRequest, $refUrl);

    $expectedUrl = "http://localhost/api{$result}/resource/987";
    $this->assertEquals($expectedUrl, $url);
  }

  public function testDefaultsWithBasePath() {
    $route = new RestRoute;

    $appRequest = new Request(
      'Resource',
      \Nette\Http\Request::GET,
      array(
        'id' => 987,
      )
    );

    $refUrl = new Url('http://localhost/base-path');
    $refUrl->setPath('/base-path/');

    $url = $route->constructUrl($appRequest, $refUrl);

    $expectedUrl = 'http://localhost/base-path/resource/987';
    $this->assertEquals($expectedUrl, $url);
  }

    public function testUrlOnSubdomain() {
        $route = new RestRoute;

        $appRequest = new Request(
            'Resource',
            \Nette\Http\Request::GET,
            array(
                'id' => 987,
            )
        );

        $refUrl = new Url('http://api.foo.bar');

        $url = $route->constructUrl($appRequest, $refUrl);

        $expectedUrl = 'http://api.foo.bar/resource/987';
        $this->assertEquals($expectedUrl, $url);
    }

    public function testQueryParams() {
      $route = new RestRoute;

      $appRequest = new Request(
        'Resource',
        \Nette\Http\Request::GET,
        array(
          'id' => 987,
          'query' => array(
            'foo' => 'bar',
            'baz' => 'bay',
          )
        )
      );

      $refUrl = new Url('http://api.foo.bar');

      $url = $route->constructUrl($appRequest, $refUrl);

      $expectedUrl = 'http://api.foo.bar/resource/987?foo=bar&baz=bay';
      $this->assertEquals($expectedUrl, $url);
    }
}
