<?php

namespace AdamStipak;

use Nette\Application\IRouter;
use Nette\InvalidArgumentException;
use Nette\Http\Request as HttpRequest;
use Nette\Application\Request;
use Nette\Http\IRequest;
use Nette\Http\Url;
use Nette\InvalidStateException;
use Nette\Utils\Strings;

/**
 * @autor Adam Štipák <adam.stipak@gmail.com>
 */
class RestRoute implements IRouter {

  /** @var string */
  protected $path;

  /** @var string */
  protected $module;

  /** @var array */
  protected $formats = array(
    'json' => 'application/json',
    'xml'  => 'application/xml',
  );

  /** @var string */
  private $requestUrl;

  /** @var string */
  protected $defaultFormat;

  const HTTP_HEADER_OVERRIDE = 'X-HTTP-Method-Override';

  const QUERY_PARAM_OVERRIDE = '__method';

  public function __construct($module = NULL, $defaultFormat = 'json') {
    if(!array_key_exists($defaultFormat, $this->formats)) {
      throw new InvalidArgumentException("Format '{$defaultFormat}' is not allowed.");
    }

    $this->module = $module;
    $this->defaultFormat = $defaultFormat;
  }

  /**
   * @return string
   */
  public function getDefaultFormat() {
    return $this->defaultFormat;
  }

  /**
   * @return string
   */
  public function getPath() {
    $path = implode('/', explode(':', $this->module));
    $this->path = strtolower($path);

    return (string) $this->path;
  }

  /**
   * Maps HTTP request to a Request object.
   * @param \Nette\Http\IRequest $httpRequest
   * @return Request|NULL
   */
  public function match(IRequest $httpRequest) {
    $url = $httpRequest->getUrl();
    $basePath = str_replace('/', '\/', $url->getBasePath());
    $cleanPath = preg_replace("/^{$basePath}/", '', $url->getPath());

    $path = str_replace('/', '\/', $this->getPath());
    $pathRexExp = empty($path) ? "/^.+$/" : "/^{$path}\/.*$/";
    if (!preg_match($pathRexExp, $cleanPath)) {
      return NULL;
    }

    $cleanPath = preg_replace('/^' . $path . '\//', '', $cleanPath);

    $params = array();
    $path = $cleanPath;
    $params['action'] = $this->detectAction($httpRequest);
    $frags = explode('/', $path);

    // Identificator.
    if (count($frags) % 2 === 0) {
      $params['id'] = array_pop($frags);
    }
    $presenterName = ucfirst(array_pop($frags));

    // Associations.
    $assoc = array();
    if (count($frags) > 0 && count($frags) % 2 === 0) {
      foreach ($frags as $k => $f) {
        if ($k % 2 !== 0) continue;

        $assoc[$f] = $frags[$k + 1];
      }
    }

    $params['format'] = $this->detectFormat($httpRequest);
    $params['associations'] = $assoc;
    $params['data'] = $this->readInput();
    $params['query'] = $httpRequest->getQuery();

    $presenterName = empty($this->module) ? $presenterName : $this->module . ':' . $presenterName;

    $this->requestUrl = $url->getAbsoluteUrl();

    $req = new Request(
      $presenterName,
      $httpRequest->getMethod(),
      $params
    );

    return $req;
  }

  protected function detectAction(HttpRequest $request) {
    $method = $this->detectMethod($request);

    switch ($method) {
      case 'GET':
        $action = 'read';
        break;
      case 'POST':
        $action = 'create';
        break;
      case 'PUT':
        $action = 'update';
        break;
      case 'DELETE':
        $action = 'delete';
        break;
      default:
        throw new InvalidStateException('Method ' . $method . ' is not allowed.');
    }

    return $action;
  }

  /**
   * @param \Nette\Http\Request $request
   * @return string
   */
  protected function detectMethod(HttpRequest $request) {
    if ($request->getMethod() !== 'POST') {
      return $request->getMethod();
    }

    $method = $request->getHeader(self::HTTP_HEADER_OVERRIDE);
    if(isset($method)) {
      return strtoupper($method);
    }

    $method = $request->getQuery(self::QUERY_PARAM_OVERRIDE);
    if(isset($method)) {
      return strtoupper($method);
    }

    return $request->getMethod();
  }

  /**
   * @param \Nette\Http\Request $request
   * @return string
   */
  private function detectFormat(HttpRequest $request) {
    $header = $request->getHeader('Accept'); // http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
    foreach ($this->formats as $k => $v) {
      $v = Strings::replace($v, '/\//', '\/');
      if(Strings::match($header, "/{$v}/")) {
        return $k;
      }
    }

    // Try retrieve fallback from URL.
    $path = $request->getUrl()->getPath();
    $formats = array_keys($this->formats);
    $formats = implode('|', $formats);
    if(Strings::match($path, "/\.({$formats})$/")) {
      list($path, $format) = explode('.', $path);
      return $format;
    }

    return $this->defaultFormat;
  }

  /**
   * @return array|null
   */
  protected function readInput() {
    return file_get_contents('php://input');
  }

  /**
   * Constructs absolute URL from Request object.
   * @param Request $appRequest
   * @param \Nette\Http\Url $refUrl
   * @throws \Nette\NotImplementedException
   * @return string|NULL
   */
  public function constructUrl(Request $appRequest, Url $refUrl) {
    return $this->requestUrl;
  }
}
