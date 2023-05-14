<?php
/**
 * OpenFairDB API
 *
 */

/**
 * Abstract Class AbstractOpenFairDBApi
 *
 * @author   Sjoerd Takken
 */
abstract class AbstractOpenFairDBApi
{
  protected $current_module;

  /**
   * @var OpenFairDBConfiguration
   */
  protected $config;

  /**
   * @var ClientInterface
   */
  protected $client;
    
  /**
   * @param ClientInterface           $client
   * @param OpenFairDBConfiguration   $config
   */
  public function __construct($current_module)
  {
    $this->current_module = $current_module;
    $client = $current_module->get_client(); 
    $config = $current_module->get_config(); 
    $this->client = $client ?: new WordpressHttpClient();
    $this->config = $config ?: new OpenFairDBConfiguration();
  }

  public function getCurrentModule()
  {
    return $this->current_module;
  }

  /**
   * @return OpenFairDBConfiguration
   */
  public function getConfig()
  {
    return $this->config;
  }

  /**
   * @return RequestInterface
   */
  protected function getRequest($method,
                                $resourcePath, 
                                $headers, 
                                $authorization = false, 
                                $queryParams = [],
                                $body = null,
                                $custom_authorization_key = null)
  {
    $httpBody = '';
    $config = $this->getConfig();

    // body params
    $_tempBody = null;

    // for model (json/xml)
    if (isset($body)) 
    {
      // $_tempBody is the method argument, if present
      // \stdClass has no __toString(), so we should encode it manually
      if (is_array($body) 
         && $headers['Content-Type'] === 'application/json') 
      {
        $httpBody = json_encode($body);
      }
    } 
        
    // // this endpoint requires Bearer token
    if ($authorization) 
    {
      if( !empty($custom_authorization_key ))
      {
        $headers['Authorization'] = 'Bearer ' . $custom_authorization_key;
      }
      else if(!empty( $config->getAccessToken()))
      {
        $headers['Authorization'] = 'Bearer ' . $config->getAccessToken();
      }
    }

    $defaultHeaders = [];
    if ($config->getUserAgent()) 
    {
      $defaultHeaders['User-Agent'] = $config->getUserAgent();
    }

    $headers = array_merge(
            $defaultHeaders,
            $headers);

    $query = $this->build_query($queryParams);
    return new SimpleRequest(
            $method,
            $config->getHost() . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody );
  }

  /**
   * Take value and turn it into a string suitable for inclusion in
   * the query, by imploding comma-separated if it's an object.
   * If it's a string, pass through unchanged. It will be url-encoded
   * later.
   *
   * @param string[]|string|\DateTime $object an object to be serialized to a string
   *
   * @return string the serialized object
   */
  public function toQueryValue($object)
  {
    if (is_array($object)) 
    {
      return implode(',', $object);
    } 
    else 
    {
      return $this->toString($object);
    }
  }

  /**
   * Take value and turn it into a string suitable for inclusion in
   * the parameter. If it's a string, pass through unchanged
   * If it's a datetime object, format it in ISO8601
   *
   * @param string|\DateTime $value the value of the parameter
   *
   * @return string the header string
   */
  public function toString($value)
  {
    if ($value instanceof DateTime) 
    { 
      // datetime in ISO8601 format
      return $value->format(DateTime::ATOM);
    } 
    else 
    {
      return $value;
    }
  }

  /**
   * Build a query string from an array of key value pairs.
   *
   * This function can use the return value of 
   * parse_query() to build a query
   * string. This function does not modify the provided keys 
   * when an array is
   * encountered (like http_build_query would).
   *
   * @param array     $params   Query string parameters.
   * @param int|false $encoding Set to false to not encode, 
   *                            PHP_QUERY_RFC3986
   *                            to encode using RFC3986, 
   *                            or PHP_QUERY_RFC1738
   *                            to encode using RFC1738.
   *
   * @return string
   */
  protected function build_query(array $params, 
                                 $encoding = PHP_QUERY_RFC3986)
  {
    if (!$params) 
    {
      return '';
    }

    if ($encoding === false) 
    {
      $encoder = function ($str) 
      {
        return $str;
      };
    } 
    elseif ($encoding === PHP_QUERY_RFC3986) 
    {
      $encoder = 'rawurlencode';
    } 
    elseif ($encoding === PHP_QUERY_RFC1738) 
    {
      $encoder = 'urlencode';
    } 
    else 
    {
      throw new InvalidArgumentException('Invalid type');
    }

    $qs = '';
    foreach ($params as $k => $v) 
    {
      $k = $encoder($k);
      if (!is_array($v)) 
      {
        $qs .= $k;
        if ($v !== null) 
        {
          $qs .= '=' . $encoder($v);
        }
        $qs .= '&';
      } 
      else 
      {
        foreach ($v as $vv) 
        {
          $qs .= $k;
          if ($vv !== null) 
          {
            $qs .= '=' . $encoder($vv);
          }
          $qs .= '&';
        }
      }
    }

    return $qs ? (string) substr($qs, 0, -1) : '';
  }

  public function convert_to_kvm_tag($tag_name)
  {
    if(empty($tag_name))
    {
      return $tag_name;
    }
    return str_replace(' ', '-', $tag_name);
  }

  public function createException($request, $response)
  {
    $statusCode = 0;
    $debug = true;

    $message = 'Error by doing HTTP ';
    $message .= $request->getMethod();
    $message .= ' request to ';
    $message .= $request->getUri();
    if($debug)
    {
      $message .= PHP_EOL;
      $message .= ' Req-Headers= {';
      foreach($request->getHeaders() as $key => $value)
      {
        $message .= ''. $key . '='. $value . ', ';
      }
      $message .= '}'. PHP_EOL;
      $message .= ' Req-Body= ';
      $message .= $request->getBody();
      $message .= '}'. PHP_EOL;
    }

    if(empty($response))
    {
      return new OpenFairDBApiException(
                    $message,
                    $statusCode);
    }

    $statusCode = $response->getStatusCode();
    $message .= 'Reason [StatusCode=';
    $message .= $statusCode;
    $message .= ']: ';
    $message .= $response->getReasonPhrase();
    if($debug)
    {
      $message .= ' Resp-Headers= {';
      foreach($response->getHeaders() as $key => $value)
      {
        $message .= ''. $key . '='. $value . ', ';
      }
      $message .= '}'. PHP_EOL;
      $message .= ' Resp-Body= {';
      $message .= $response->getBody();
      $message .= '}'. PHP_EOL;
    }
    return new OpenFairDBApiException(
                    $message,
                    $statusCode,
                    $response->getHeaders(),
                    $response->getBody());
  }
}
