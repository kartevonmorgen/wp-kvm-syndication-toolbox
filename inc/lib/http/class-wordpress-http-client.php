<?php

class WordpressHttpClient implements ClientInterface
{
  /**
   * @var WP_Http
   */
  protected $wpHttp;

  /**
   * @param WP_Http $wpHttp
   */
  public function __construct(WP_Http $wpHttp = null)
  {
    $this->wpHttp  = $wpHttp  ? $wpHttp  : new WP_Http();
  }

  public function send(RequestInterface $request ): ResponseInterface
  {
    return $this->internSend($request, true);
  }

  public function sendAsync(RequestInterface $request ): ResponseInterface
  {
    return $this->internSend($request, false);
  }

  private function internSend(RequestInterface $request, 
                 bool $blocking ): ResponseInterface
  {
    $params = array('blocking' => $blocking);

    if( !empty( $request->getHeaders()))
    {
      $params['headers'] = $request->getHeaders();
    }

    if( !empty( $request->getBody() ))
    {
      $params['body'] = $request->getBody();
    }

    return $this->request($request->getMethod(), 
                          $request->getUri(), 
                          $params);
  }

  /**
   * {@inheritdoc}
   */
  private function request($method, $uri, array $params = array())
  {
    if (! method_exists($this->wpHttp, $method)) 
    {
      $params['method'] = strtoupper($method);
      $method = 'request';
    }

    $result = call_user_func([$this->wpHttp, $method], $uri, $params);

    if (is_array($result)) 
    {
      return $this->createFromArray($result);
    }

    if ($result instanceof WP_Error) 
    {
      return $this->createFromWpError($result);
    }

    return new SimpleResponse(500);
  }

  protected function createFromArray(array $data)
  {
    if (!isset($data['response']) || !isset($data['response']['code'])) 
    {
      return new SimpleResponse(500);
    }

    $headers = array();

    $body = isset($data['body']) ? strval($data['body']) : null;
    if(isset($data['headers']))
    {
      if(is_array($data['headers']))
      {
        $headers = $data['headers'];
      }
      else if ($data['headers'] instanceof 
               Requests_Utility_CaseInsensitiveDictionary)
      {
        $headers = $data['headers']->getAll();
      }
    }
    $statusCode = (int) $data['response']['code'];

    return new SimpleResponse($statusCode, $headers, $body);
  }

  protected function createFromWpError(WP_Error $error)
  {
    return new SimpleResponse(500, array(), join("\n", $error->get_error_messages()));
  }
}
