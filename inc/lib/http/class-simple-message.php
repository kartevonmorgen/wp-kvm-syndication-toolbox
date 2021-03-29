<?php

abstract class SimpleMessage
{
  /** @var array Map of all registered headers, 
   * as original name => array of values */
  private $_headers = array();

  /** @var array Map of lowercase header name => 
   * original name at registration */
  private $_headerNames = array();

  /** @var string */
  private $_protocol = '1.1';

  private $_body = null;

  public function getProtocolVersion()
  {
    return $this->_protocol;
  }
  
  public function getHeaders()
  {
    return $this->_headers;
  }

  public function hasHeader($header)
  {
    return isset($this->_headerNames[strtolower($header)]);
  }

  public function getHeader($header)
  {
    $header = strtolower($header);

    if (!isset($this->_headerNames[$header])) 
    {
      return array();
    }

    $header = $this->_headerNames[$header];
    return $this->headers[$header];
  }

  public function setProtocolVersion($protocol)
  {
    $this->_protocol = $protocol;
  }

  public function setHeaders(array $headers): void
  {
    $this->_headerNames = array();
    $this->_headers = array();

    foreach ($headers as $header => $value) 
    {
      if (is_int($header)) 
      {
        // Numeric array keys are converted to int by PHP 
        // but having a header name '123' is not forbidden by the spec
        // and also allowed in withHeader(). So we need to cast it to string again for the following assertion to pass.
        $header = (string) $header;
      }
      
      $this->assertHeader($header);
      $value = $this->normalizeHeaderValue($value);
      $normalized = strtolower($header);
      $this->_headerNames[$normalized] = $header;
      $this->_headers[$header] = $value;
    }
  }

  private function normalizeHeaderValue($value)
  {
    return $this->trimHeaderValue($value);
  }

  /**
   * Trims whitespace from the header values.
   *
   * Spaces and tabs ought to be excluded by parsers 
   * when extracting the field value from a header field.
   *
   * header-field = field-name ":" OWS field-value OWS
   * OWS          = *( SP / HTAB )
   *
   * @param string[] $values Header values
   * @return string[] Trimmed header values
   * @see https://tools.ietf.org/html/rfc7230#section-3.2.4
   */
  private function trimHeaderValue($value)
  {
    if(is_array($value))
    {
      $result = array();
      foreach($value as $valueelement)
      {
        array_push($result, trim($valueelement, " \t"));
      }
      return $result;
    }
    return trim($value, " \t");
  }

  /**
   * @see https://tools.ietf.org/html/rfc7230#section-3.2
   */
  private function assertHeader($header)
  {
    if (!is_string($header)) 
    {
      throw new InvalidArgumentException(sprintf(
        'Header name must be a string but %s provided.',
        is_object($header) ? get_class($header) : gettype($header)
        ));
    }

    if (! preg_match('/^[a-zA-Z0-9\'`#$%&*+.^_|~!-]+$/', $header)) 
    {
      throw new InvalidArgumentException(
        sprintf(
          '"%s" is not valid header name',
          $header));
    }
  }  

  public function setBody($body)
  {
    $this->_body = $body;
  }

  public function getBody()
  {
    return $this->_body;
  }


}
