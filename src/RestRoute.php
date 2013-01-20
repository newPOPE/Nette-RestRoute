<?php

namespace AdamStipak;

use Nette\Application\IRouter;
use Nette\NotImplementedException;
use Nette\InvalidStateException;
use Nette\Http\Request as HttpRequest;
use Nette\Application\Request;
use Nette\Http\IRequest;
use Nette\Http\Url;

/**
 * @autor Adam Štipák <adam.stipak@gmail.com>
 */
class RestRoute implements IRouter {

  /** @var string */
  protected $path;

  /** @var string */
  protected $module;

  /** @var array */
  protected $formats = array('json');

  const HTTP_HEADER_OVERRIDE = 'X-HTTP-Method-Override';

  const QUERY_PARAM_OVERRIDE = '__method';

  public function __construct($module, array $formats) {
    $this->module = $module;
    $this->formats = $formats;
  }

  /**
   * @return string
   */
  public function getPath() {
    if (!$this->path) {
      $path = implode('/', explode(':', $this->module));
      $this->path = strtolower($path);
    }

    return $this->path;
  }

  /**
   * Maps HTTP request to a Request object.
   * @param \Nette\Http\IRequest $httpRequest
   * @return Request|NULL
   */
  public function match(IRequest $httpRequest) {
    $basePath = str_replace('/', '\/', $httpRequest->getUrl()->getBasePath());
    $cleanPath = preg_replace("/^{$basePath}/", '', $httpRequest->getUrl()->getPath());

    $formats = implode('|', $this->formats);
    $path = str_replace('/', '\/', $this->getPath());
    if (!preg_match("/^{$path}\/.+\.({$formats})$/", $cleanPath)) {
      return NULL;
    }

    $cleanPath = preg_replace('/^' . $path . '\//', '', $cleanPath);

    $params = array();
    list($path, $params['format']) = explode('.', $cleanPath);
    $this->checkFormat($params['format']);
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

    $params['associations'] = $assoc;
    $params['data'] = $this->readInput();
    $params['query'] = $httpRequest->getQuery();

    $req = new Request(
      $this->module . ':' . $presenterName,
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
   * @param $path
   * @throws \Nette\NotImplementedException
   * @return string
   */
  protected function checkFormat($path) {
    $frags = explode('.', $path);
    $format = end($frags);

    if (!in_array($format, $this->formats)) {
      throw new NotImplementedException("Format {$format} is not supported.");
    }
    return $format;
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
    $cleanPath = str_replace($refUrl->getBasePath(), '', $refUrl->getPath());

    $url = $this->getPath() . '/';
    $params = $appRequest->getParameters();

    if(!isset($params['associations'])) {
      return NULL;
    }

    foreach ($params['associations'] as $k => $v) {
      $url .= $k . '/' . $v;
    }

    $resource = explode(':', $appRequest->getPresenterName());
    $resource = end($resource);
    $resource = strtolower($resource);
    $url .= (count($params['associations']) ? '/' : '') . $resource;

    if (!empty($params['id'])) {
      $url .= '/' . $params['id'];
    }

    if(!isset($params['format'])) {
      return NULL;
    }

    $url .= '.' . $params['format'];

    if (count($params['query'])) {
      $url .= '?' . http_build_query($params['query']);
    }

    $formats = implode('|', $this->formats);
    $path = str_replace('/', '\/', $this->getPath());
    if (!preg_match("/^{$path}\/.+\.({$formats})/", $url)) {
      return NULL;
    }

    return $refUrl->baseUrl . $url;
  }
}
