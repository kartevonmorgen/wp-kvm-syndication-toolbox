<?php
/**
 */

/**
 * OpenFairDBEntriesApi Class Doc Comment
 *
 * @category Class
 * @package  Swagger\Client
 * @author   Swagger Codegen team
 * @link     https://github.com/swagger-api/swagger-codegen
 */
class OpenFairDBEntriesApi extends AbstractOpenFairDBApi
{
 /**
  * Operation entriesIdsGet
  *
  * Get multiple entries
  *
  * @param  array ids (required)
  *
  * @throws OpenFairDBApiException on non-2xx response
  * @throws InvalidArgumentException
  * @return array of WPOrganisation
  */
  public function entriesGet($ids)
  {
    $request = $this->entriesGetRequest($ids);

    $response = $this->client->send($request);
    $statusCode = $response->getStatusCode();
    if ($statusCode < 200 || $statusCode > 299) 
    {
      throw $this->createException($request, $response);
    }

    $responseBody = $response->getBody();
    $kvm_entries = array();
    $entryArr = json_decode($responseBody, true);
    foreach($entryArr as $entry)
    {
      $kvm_entry = $this->createEntry($entry);
      array_push( $kvm_entries, $kvm_entry);
    }
    return $kvm_entries;
  }

  /**
   * Create request for operation 'entriesIdsGet'
   *
   * @param  array $ids (required)
   *
   * @throws InvalidArgumentException
   * @return RequestInterface
   */
  protected function entriesGetRequest($ids)
  {
    // verify the required parameter 'ids' is set
    if (empty($ids)) 
    {
      throw new InvalidArgumentException(
        'Missing the required parameter $ids'.
        ' when calling entriesIdsGet');
    }

    $resourcePath = '/entries/'. implode(',', $ids);

    $headers = array();
    $headers['Accept'] = 'application/json';

    return $this->getRequest('GET',
                             $resourcePath, 
                             $headers);       
  }

  public function entriesPut($entry, $id)
  {
    $body = $entry->get_body();
    $body['id'] = $id;
    $request = $this->entriesPutRequest($body, $id);

    $response = $this->client->send($request);
    $statusCode = $response->getStatusCode();
    if ($statusCode < 200 || $statusCode > 299) 
    {
      throw $this->createException($request, $response);
    }
  }

  public function entriesPutAsync($entry, 
                                  $id) 
  {
    $body = $entry->get_body();
    $body['id'] = $id;
    $request = $this->entriesPutRequest($body, $id);
    $this->client->sendAsync($request);
  }
    
  protected function entriesPutRequest($body, $id)
  {
    // verify the required parameter 'body' is set
    if (empty($body)) 
    {
      throw new InvalidArgumentException(
                'Missing the required parameter $body' .
                ' when calling entriesIdPut');
    }
    
    // verify the required parameter 'id' is set
    if (empty($id)) 
    {
      throw new InvalidArgumentException(
                'Missing the required parameter $id' .
                ' when calling entriesIdPut');
    }

    $resourcePath = '/entries/' . $id;

    $headers = array();
    $headers['Content-Type'] = 'application/json';

    return $this->getRequest('PUT',
                             $resourcePath, 
                             $headers, 
                             true,
                             array(),
                             $body);
  }

  /**
   * Operation entriesPost
   *
   * Create an entry
   *
   * @param  \Swagger\Client\Model\Entry $body body (required)
   *
   * @throws OpenFairDBApiException on non-2xx response
   * @throws InvalidArgumentException
   * @return string
   */
  public function entriesPost($entry)
  {
    $body = $entry->get_body();
    $request = $this->entriesPostRequest($body);

    $response = $this->client->send($request);
    $statusCode = $response->getStatusCode();
    if ($statusCode < 200 || $statusCode > 299) 
    {
      throw $this->createException($request, $response);
    }
    return json_decode($response->getBody());
  }

  /**
   * Operation entriesPostAsync
   *
   * Create an entry
   *
   * @param  $wpOrganisation (required)
   *
   * @throws InvalidArgumentException
   * @return ResponseInterface
   */
  public function entriesPostAsync($entry)
  {
    $body = $entry->get_body();
    $request = $this->entriesPostRequest($body);
    return $this->client->sendAsync($request);     
  }

  /**
   * Create request for operation 'entriesPost'
   *
   * @param  \Swagger\Client\Model\Entry $body (required)
   *
   * @throws \InvalidArgumentException
   * @return \GuzzleHttp\Psr7\Request
   */
  protected function entriesPostRequest($body)
  {
    // verify the required parameter 'body' is set
    if (empty($body)) 
    {
      throw new InvalidArgumentException(
        'Missing the required parameter $body when calling entriesPost');
    }

    $resourcePath = '/entries';
    $headers = array();
    $headers['Content-Type'] = 'application/json';
    return $this->getRequest('POST',
                             $resourcePath, 
                             $headers, 
                             true,
                             array(),
                             $body);
  }

  /**
   * Operation searchGet
   *
   * Search for entries
   *
   * @param  string $bbox Bounding Box (optional)
   * @param  string $categories Comma-separated list of category identifiers. We currently use the following two: 
   *  -Organisation (non-commercial): &#x60;2cd00bebec0c48ba9db761da48678134&#x60; 
      -Company (commercial): &#x60;77b3c33a92554bcf8e8c2c86cedd6f6f&#x60; 
   * @param  string $text text (optional)
   * @param  \Swagger\Client\Model\IdList $ids ids (optional)
   * @param  \Swagger\Client\Model\TagList $tags tags (optional)
   * @param  \Swagger\Client\Model\ReviewStatusList $status status (optional)
   * @param  int $limit Maximum number of items to return or implicit/unlimited if unspecified. (optional)
   *
   * @throws OpenFairDBApiException on non-2xx response
   * @throws InvalidArgumentException
   * @return WPOrganisation[] 
   */
  public function searchGet($bbox = null, 
                            $categories = null, 
                            $text = null, 
                            $ids = null, 
                            $tags = null, 
                            $status = null, 
                            $limit = null)
  {
    $request = $this->searchGetRequest($bbox, $categories, $text, $ids, $tags, $status, $limit);

    $response = $this->client->send($request);
    $statusCode = $response->getStatusCode();
    if ($statusCode < 200 || $statusCode > 299) 
    {
      throw $this->createException($request, $response);
    }
    $responseBody = $response->getBody();

    $body = json_decode($responseBody, true);
    $wpOrganisationArr = $body['visible'];
    $kvm_entries = array();
    foreach($wpOrganisationArr as $wpOrganisationData)
    {
      $kvm_entry = $this->createEntry($wpOrganisationData);
      array_push( $kvm_entries, $kvm_entry);
    }
    return $kvm_entries;
  }

  public function confirmEntry($id, $comment)
  {
    $token = $this->login();
    $this->placesReview($token, array($id), "confirmed", $comment);
    //$this->logout($token);
  }

  public function archiveEntry($id, $comment)
  {
    $token = $this->login();
    $this->placesReview($token, array($id), "archived", $comment);
    //$this->logout($token);
  }

  public function placesReview($token, $ids, $status, $comment)
  {
    $request = $this->placesReviewRequest($token, $ids, $status, $comment);
    $this->client->send($request);
  }

  protected function placesReviewRequest($token, $ids, $status, $comment)
  {
    // verify the required parameter 'ids' is set
    if (empty($ids)) 
    {
      throw new InvalidArgumentException(
                'Missing the required parameter $ids' .
                ' when calling placesReviewRequest');
    }
    
    // verify the required parameter 'status' is set
    if (empty($status)) 
    {
      throw new InvalidArgumentException(
                'Missing the required parameter $status' .
                ' when calling placesReviewRequest');
    }

    // verify the required parameter 'comment' is set
    if (empty($comment)) 
    {
      throw new InvalidArgumentException(
                'Missing the required parameter $comment' .
                ' when calling placesReviewRequest');
    }

    $idsString = implode($ids);
    $body = array();
    $body['status'] = $status;
    $body['comment'] = $comment;

    $resourcePath = '/places/' . $idsString . '/review';

    $headers = array();
    $headers['Content-Type'] = 'application/json';

    return $this->getRequest('POST',
                             $resourcePath, 
                             $headers, 
                             true,
                             array(),
                             $body,
                             $token);
  }
  


  /**
   * Create request for operation 'searchGet'
   *
   * @param  string $bbox Bounding Box (optional)
   * @param  string $categories Comma-separated list of category identifiers. We currently use the following two: - Organisation (non-commercial): &#x60;2cd00bebec0c48ba9db761da48678134&#x60; - Company (commercial): &#x60;77b3c33a92554bcf8e8c2c86cedd6f6f&#x60; (optional)
   * @param  string $text (optional)
   * @param  \Swagger\Client\Model\IdList $ids (optional)
   * @param  \Swagger\Client\Model\TagList $tags (optional)
   * @param  \Swagger\Client\Model\ReviewStatusList $status (optional)
   * @param  int $limit Maximum number of items to return or implicit/unlimited if unspecified. (optional)
   *
   * @throws InvalidArgumentException
   * @return RequestInterface
   */
  protected function searchGetRequest($bbox = null, 
                                      $categories = null, 
                                      $text = null, 
                                      $ids = null, 
                                      $tags = null, 
                                      $status = null, 
                                      $limit = null)
  {
    $resourcePath = '/search';
    $queryParams = [];

    $formParams = [];
    $headerParams = [];
    $httpBody = '';
    $multipart = false;

    // query params
    if ($bbox !== null) 
    {
      $queryParams['bbox'] = $this->toQueryValue($bbox);
    }

    // query params
    if ($categories !== null) 
    {
      $queryParams['categories'] = $this->toQueryValue($categories);
    }

    // query params
    if ($text !== null) 
    {
      $queryParams['text'] = $this->toQueryValue($text);
    }

    // query params
    if ($ids !== null) 
    {
      $queryParams['ids'] = $this->toQueryValue($ids);
    }
    
    // query params
    if ($tags !== null) 
    {
      $queryParams['tags'] = $this->toQueryValue($tags);
    }

    // query params
    if ($status !== null) 
    {
      $queryParams['status'] = $this->toQueryValue($status);
    }

    // query params
    if ($limit !== null) 
    {
      $queryParams['limit'] = $this->toQueryValue($limit);
    }

    // body params
    $headers = array();
    $headers['Accept'] = 'application/json';
    return $this->getRequest('GET',
                             $resourcePath, 
                             $headers, 
                             false, 
                             $queryParams);       
  }

  protected function login()
  {
    $request = $this->loginRequest();
    $response = $this->client->send($request);
    $statusCode = $response->getStatusCode();
    if ($statusCode < 200 || $statusCode > 299) 
    {
      throw $this->createException($request, $response);
    }
    $responseBody = $response->getBody();

    $body = json_decode($responseBody, true);
    $token = $body['token'];
    return $token;
  }

  protected function logout($token)
  {
    $request = $this->loginRequest($token);
    $response = $this->client->send($request);
    $statusCode = $response->getStatusCode();
    if ($statusCode < 200 || $statusCode > 299) 
    {
      throw $this->createException($request, $response);
    }
  }

  private function loginRequest()
  {
    $config = $this->getConfig();

    $body = array();
    $body['email'] = $config->getUsername();
    $body['password'] = $config->getPassword();

    $resourcePath = '/login';

    $headers = array();
    $headers['Content-Type'] = 'application/json';

    return $this->getRequest('POST',
                             $resourcePath, 
                             $headers, 
                             false,
                             array(),
                             $body);
  }

  private function logoutRequest($token)
  {
    $resourcePath = '/logout';

    $headers = array();

    return $this->getRequest('POST',
                             $resourcePath, 
                             $headers, 
                             true,
                             array(),
                             null,
                             $token);
  }

  private function createEntry($body)
  {
    $module = $this->getCurrentModule();
    return new KVMEntry($module, $body);
  }


}
