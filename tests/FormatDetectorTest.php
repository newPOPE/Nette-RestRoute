<?php

namespace AdamStipak;

use Nette\Http\Request;
use Nette\Http\UrlScript;
use Nette\Reflection\Method;

class FormatDetectorTest extends \PHPUnit_Framework_TestCase {

  private function runDetectFormatMethod($route, $request) {
    $method = new Method($route, 'detectFormat');
    $method->setAccessible(TRUE);
    return $method->invoke($route, $request);
  }

  public function testFormatJsonWithAcceptHeader() {
    $route = new RestRoute('Api');

    $url = new UrlScript();
    $request = new Request(
      $url, NULL, NULL, NULL, ['accept' => 'application/json']
    );
    $format = $this->runDetectFormatMethod($route, $request);

    $this->assertEquals('json', $format);
  }

  public function testFormatXmlWithAcceptHeader() {
    $route = new RestRoute('Api');

    $url = new UrlScript();
    $request = new Request(
      $url, NULL, NULL, NULL, ['accept' => 'application/xml']
    );
    $format = $this->runDetectFormatMethod($route, $request);

    $this->assertEquals('xml', $format);
  }

  public function testDefaultFormatWithWildcardHeader() {
    $route = new RestRoute('Api');

    $url = new UrlScript();
    $request = new Request(
      $url, NULL, NULL, NULL, ['accept' => '*/*']
    );
    $format = $this->runDetectFormatMethod($route, $request);

    $this->assertEquals('json', $format);
  }

  public function testJsonFormatWithFallbackInUrl() {
    $route = new RestRoute('Api');

    $url = (new UrlScript())->withPath('/api/foo.json');
    $request = new Request($url);
    $format = $this->runDetectFormatMethod($route, $request);

    $this->assertEquals('json', $format);
  }

  public function testXmlFormatWithFallbackInUrl() {
    $route = new RestRoute('Api');

    $url = (new UrlScript())->withPath('/api/foo.xml');
    $request = new Request($url);
    $format = $this->runDetectFormatMethod($route, $request);

    $this->assertEquals('xml', $format);
  }

  public function testDefaultFormat() {
    $route = new RestRoute('Api');

    $url = (new UrlScript())->withPath('/api/foo');
    $request = new Request($url);
    $format = $this->runDetectFormatMethod($route, $request);

    $this->assertEquals('json', $format);
  }

}
