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

  public function testWithModuleAndAssociations() {
    $route = new RestRoute('Api');

    $appRequest = new Request(
      'Api:Resource',
      \Nette\Http\Request::GET,
      array(
        'id' => 987,
        'associations' => array(
          'foos' => 123,
          'bars' => 234,
        )
      )
    );

    $refUrl = new Url('http://localhost/');

    $url = $route->constructUrl($appRequest, $refUrl);

    $expectedUrl = 'http://localhost/api/foos/123/bars/234/resource/987';
    $this->assertEquals($expectedUrl, $url);
  }
}
