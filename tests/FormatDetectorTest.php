<?php

namespace AdamStipak;

use Nette\Http\Request;
use Nette\Http\UrlScript;
use ReflectionMethod;

class FormatDetectorTest extends \PHPUnit\Framework\TestCase {

  private function runDetectFormatMethod($route, $request) {
    $method = new ReflectionMethod($route, 'detectFormat');
    $method->setAccessible(TRUE);
    return $method->invoke($route, $request);
  }

  public function testFormatJsonWithAcceptHeader() {
    $route = new RestRoute('Api');

    $url = new UrlScript();
    $request = new Request(
      $url, [], [], [], ['accept' => 'application/json']
    );
    $format = $this->runDetectFormatMethod($route, $request);

    $this->assertEquals('json', $format);
  }

  public function testFormatXmlWithAcceptHeader() {
    $route = new RestRoute('Api');

    $url = new UrlScript();
    $request = new Request(
      $url, [], [], [], ['accept' => 'application/xml']
    );
    $format = $this->runDetectFormatMethod($route, $request);

    $this->assertEquals('xml', $format);
  }

  public function testDefaultFormatWithWildcardHeader() {
    $route = new RestRoute('Api');

    $url = new UrlScript();
    $request = new Request(
      $url, [], [], [], ['accept' => '*/*']
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
