<?php

class SimpleRequest extends SimpleMessage implements RequestInterface
{
  /** @var string */
  private $_method;

  /** @var string */
  private $_uri;

  /**
   * @param string  $method  HTTP method
   * @param string  $uri     URI
   * @param array   $headers Request headers
   * @param string  $body    Request body
   * @param string  $version Protocol version
     */
  public function __construct(
        string $method,
        $uri,
        array $headers = array(),
        $body = null,
        string $version = '1.1') 
  {
    $this->_method = strtoupper($method);
    $this->_uri = $uri;
    $this->setHeaders($headers);
    $this->setBody($body);
    $this->setProtocolVersion($version);
  }

  public function getMethod()
  {
    return $this->_method;
  }

  public function getUri()
  {
    return $this->_uri;
  }    

}
