<?php
/**
 * OpenFairDB API
 *
 */

/**
 * OpenFairDBEventsApi Class Doc Comment
 *
 * @author   Sjoerd Takken
 * @link     https://github.com/kartevonmorgen/kvm-interface
 */
class OpenFairDBEventsApi extends AbstractOpenFairDBApi
{

  /**
   * Operation eventsGet
   *
   * Search events
   *
   * @param  string $bbox Bounding Box (optional)
   * @param  int $limit Maximum number of items to return or implicit/unlimited if unspecified. (optional)
   * @param  OpenFairDBTagList $tag Filter events by tags (optional)
   * @param  OpenFairDBEventTime $start_min Filter events by &#x60;event.start&#x60; &gt;&#x3D; &#x60;start_min&#x60; (optional)
   * @param  OpenFairDBEventTime $start_max Filter events by &#x60;event.start&#x60; &lt;&#x3D; &#x60;start_max&#x60; (optional)
   * @param  string $text Filter events by textual terms. Hashtags starting with &#x27;#&#x27; will be extracted from the given text and handled as tag filters. (optional)
   * @param  OpenFairDBEmail $created_by The email address of the event creator. Requests with this parameter will be rejected without a valid API token! (optional)
   *
   * @throws OpenFairDBApiException on non-2xx response
   * @throws InvalidArgumentException
   * @return EICalendarEvent[]
   */
  public function eventsGet($bbox = null, $limit = null, 
                            $tag = null, 
                            $start_min = null, $start_max = null, 
                            $text = null)
  {
    $created_by = get_option('admin_email');

    $request = $this->eventsGetRequest($bbox, $limit, 
                                       $tag, 
                                       $start_min, $start_max, 
                                       $text, $created_by);

    $response = $this->client->send($request);
    $statusCode = $response->getStatusCode();
    if ($statusCode < 200 || $statusCode > 299) 
    {
      throw $this->createException($request, $response);
    }

    $responseBody = $response->getBody();

    $eiEvents = array();
    $eventsArr = json_decode($responseBody, true);
    if(!empty($eventsArr))
    {
      foreach($eventsArr as $event)
      {
        $eiEvent = $this->createEvent($event);
        array_push( $eiEvents, $eiEvent);
      }
    }
    return $eiEvents;
  }

  /**
   * Create request for operation 'eventsGet'
   *
   * @param  string $bbox Bounding Box (optional)
   * @param  int $limit Maximum number of items to return or implicit/unlimited if unspecified. (optional)
   * @param  OpenFairDBTagList $tag Filter events by tags (optional)
   * @param  OpenFairDBEventTime $start_min Filter events by &#x60;event.start&#x60; &gt;&#x3D; &#x60;start_min&#x60; (optional)
   * @param  OpenFairDBEventTime $start_max Filter events by &#x60;event.start&#x60; &lt;&#x3D; &#x60;start_max&#x60; (optional)
   * @param  string $text Filter events by textual terms. Hashtags starting with &#x27;#&#x27; will be extracted from the given text and handled as tag filters. (optional)
   * @param  OpenFairDBEmail $created_by The email address of the event creator. Requests with this parameter will be rejected without a valid API token! (optional)
   *
   * @throws InvalidArgumentException
   * @return RequestInterface
   */
  protected function eventsGetRequest($bbox = null, $limit = null, $tag = null, $start_min = null, $start_max = null, $text = null, $created_by = null)
  {
    $resourcePath = '/events';
    $queryParams = [];

    // query params
    if ($bbox !== null) 
    {
      $queryParams['bbox'] = $this->toQueryValue($bbox);
    }
      
    // query params
    if ($limit !== null) 
    {
      $queryParams['limit'] = $this->toQueryValue($limit);
    }
     
    // query params
    if ($tag !== null) 
    {
      $queryParams['tag'] = $this->toQueryValue($tag);
    }
     
    // query params
    if ($start_min !== null) 
    {
      $queryParams['start_min'] = $this->toQueryValue($start_min);
    }
     
    // query params
    if ($start_max !== null) 
    {
      $queryParams['start_max'] = $this->toQueryValue($start_max);
    }
     
    // query params
    if ($text !== null) 
    {
      $queryParams['text'] = $this->toQueryValue($text);
    }
     
    // query params
    if ($created_by !== null) 
    {
      $queryParams['created_by'] = $this->toQueryValue($created_by);
    }

    // body params
    $headers = array();
    $headers['Accept'] = 'application/json';
    return $this->getRequest('GET',
                             $resourcePath, 
                             $headers, 
                             true, 
                             $queryParams);       
  }

  /**
   * Operation eventsIdDelete
   *
   * Delete an event
   *
   * @param  string $id id (required)
   *
   * @throws OpenFairDBApiException on non-2xx response
   * @throws InvalidArgumentException
   * @return void
   */
  public function eventsDelete($id)
  {
    $request = $this->eventsIdDeleteRequest($id);
    $response = $this->client->send($request);

    $statusCode = $response->getStatusCode();
    if ($statusCode < 200 || $statusCode > 299) 
    {
      throw $this->createException($request,$response);
    }
  }


  /**
   * Operation eventsIdDeleteAsync
   *
   * Delete an event
   *
   * @param  string $id (required)
   *
   * @throws InvalidArgumentException
   */
  public function eventsDeleteAsync($id)
  {
    $request = $this->eventsIdDeleteRequest($id);
    return $this->client->sendAsync($request);
  }

 /**
   * Create request for operation 'eventsIdDelete'
   *
   * @param  string $id (required)
   *
   * @throws InvalidArgumentException
   */
  protected function eventsIdDeleteRequest($id)
  {
    // verify the required parameter 'id' is set
    if (empty($id)) 
    {
      throw new InvalidArgumentException(
        'Missing the required parameter $id ' .
        'when calling eventsIdDelete');
    }

    $resourcePath = '/events/' . $id;
    $headers = array(); 
    return $this->getRequest('DELETE',
                            $resourcePath, 
                            $headers, 
                            true);
  }

  /**
   * Operation eventsIdGet
   *
   * Get a single event
   *
   * @param  string $id id (required)
   *
   * @throws OpenFairDBApiException on non-2xx response
   * @throws InvalidArgumentException
   * @return EICalendarEvent
     */
  public function eventsIdGet($id)
  {
    $request = $this->eventsIdGetRequest($id);

    $response = $this->client->send($request);
    $statusCode = $response->getStatusCode();
    if ($statusCode < 200 || $statusCode > 299) 
    {
      throw $this->createException($request, 
                                   $response);
    }

    $content = $response->getBody();
    $content = json_decode($content);
    return $this->createEvent($content);
  }

  /**
   * Create request for operation 'eventsIdGet'
   *
   * @param  string $id (required)
   *
   * @throws InvalidArgumentException
   * @return RequestInterface
   */
  protected function eventsIdGetRequest($id)
  {
    // verify the required parameter 'id' is set
    if (empty($id)) 
    {
      throw new InvalidArgumentException(
        'Missing the required parameter $id ' . 
        'when calling eventsIdGet');
    }

    $resourcePath = '/events/'. $id;
    $headers = array();
    $headers['Accept'] = 'application/json';
    return $this->getRequest('GET',
                             $resourcePath, 
                             $headers);        
  }

  /**
   * Operation eventsIdPut
   *
   * Update an event
   *
   * @param  OpenFairDBEvent $body body (required)
   * @param  string $id id of the OpenFairDbEvent(required)
   *
   * @throws OpenFairDBApiException on non-2xx response
   * @throws InvalidArgumentException
   * @return void
   */
  public function eventsPut($eiEvent, $id)
  {
    $body = $this->createBody($eiEvent);
    $body['id'] = $id;
    $request = $this->eventsIdPutRequest($body, $id);

    $response = $this->client->send($request);
    $statusCode = $response->getStatusCode();
    if ($statusCode < 200 || $statusCode > 299) 
    {
      throw $this->createException($request, 
                                   $response);
    } 
  }

  /**
   * Operation eventsIdPutAsync
   *
   * Update an event
   *
   * @param  EICalendarEvent $eiEvent (required)
   * @param  string $id of OpenFairDB Event (required)
   *
   * @throws InvalidArgumentException
   */
  public function eventsPutAsync($eiEvent, $id)
  {
    $body = $this->createBody($eiEvent);
    $body['id'] = $id;
    $request = $this->eventsIdPutRequest($body, $id);
    $this->client->sendAsync($request);
  }

  /**
   * Create request for operation 'eventsIdPut'
   *
   * @param  array $body (required)
   * @param  string $id (required)
   *
   * @throws InvalidArgumentException
   * @return RequestInterface
   */
  protected function eventsIdPutRequest($body, $id)
  {
    // verify the required parameter 'body' is set
    if (empty($body)) 
    {
      throw new InvalidArgumentException(
        'Missing the required parameter $body ' .
        'when calling eventsIdPut');
    }
    // verify the required parameter 'id' is set
    if (empty($id)) 
    {
      throw new InvalidArgumentException(
        'Missing the required parameter $id ' .
        'when calling eventsIdPut');
    }

    $resourcePath = '/events/' . $id;

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
   * Operation eventsPost
   *
   * Create a new event
   *
   * @param  OpenFairDBEvent $body body (required)
   *
   * @throws OpenFairDBApiException on non-2xx response
   * @throws InvalidArgumentException
   * @return string
   */
  public function eventsPost($eiEvent)
  {
    $body = $this->createBody($eiEvent);
    $request = $this->eventsPostRequest($body);

    $response = $this->client->send($request);
    $statusCode = $response->getStatusCode();

    if ($statusCode < 200 || $statusCode > 299) 
    {
       throw $this->createException($request, 
                                      $response);
    }

    return json_decode($response->getBody());
  }

  /**
   * Operation eventsPostAsync
   *
   * Create a new event
   *
   * @param  EICalendarEvent $eiEvent (required)
   *
   * @throws InvalidArgumentException
   * @return ResponseInterface
   */
  public function eventsPostAsync($eiEvent)
  {
    $body = $this->createBody($eiEvent);
    $request = $this->eventsPostRequest($body);
    return $this->client->sendAsync($request);     
  }


  /**
   * Create request for operation 'eventsPost'
   *
   * @param  OpenFairDBEvent $body (required)
   *
   * @throws InvalidArgumentException
   * @return RequestInterface
   */
  protected function eventsPostRequest($body)
  {
    // verify the required parameter 'body' is set
    if (empty($body)) 
    {
      throw new InvalidArgumentException(
        'Missing the required parameter $body when calling eventsPost');
    }

    $resourcePath = '/events';

    $headers = array();
    $headers['Accept'] = 'application/json';
    $headers['Content-Type'] = 'application/json';

    return $this->getRequest('POST',
                             $resourcePath, 
                             $headers, 
                             true,
                             array(),
                             $body);
  }

  private function createBody($eiEvent)
  {
    $admin_email = get_option('admin_email');

    $body = array();
    $body['title'] = $eiEvent->get_title();
    if($eiEvent->has_excerpt())
    {
      $body['description'] = $eiEvent->get_excerpt();
    }
    else
    {
      $body['description'] = $eiEvent->generate_excerpt();
    }
    $body['created_by'] = $admin_email;
    $body['start'] = $eiEvent->get_start_date_unixtime();
    $body['end'] = $eiEvent->get_end_date_unixtime();
    if(!empty($eiEvent->get_contact_name()))
    {
      $body['organizer'] = $eiEvent->get_contact_name(); 
    }
    //Do not upload Email, to work against spam
    if(!empty($eiEvent->get_contact_email()))
    {
      $body['email'] = $eiEvent->get_contact_email(); 
    }
    //$body['email'] = ''; 
    if(!empty($eiEvent->get_contact_phone()))
    {
      $body['telephone'] = $eiEvent->get_contact_phone(); 
    }
    $body['homepage'] = $eiEvent->get_link();
    
    $eiLocation = $eiEvent->get_location();
    if(!empty($eiLocation))
    {
      $wpLocH = new WPLocationHelper();
      $address = $wpLocH->get_address($eiLocation);
      
      if(!empty($address))
      {
        $body['street'] = $address;
      }
      
      if(!empty($eiLocation->get_zip()))
      {
        $body['zip'] = $eiLocation->get_zip();
      }
      
      if(!empty($eiLocation->get_city()))
      {
        $body['city'] = $eiLocation->get_city();
      }
      
      if(!empty($eiLocation->get_country_code()))
      {
        $body['country'] = $eiLocation->get_country_code();
      }
      
      if(!empty($eiLocation->get_state()))
      {
        $body['state'] = $eiLocation->get_state();
      }

      if(!empty($eiLocation->get_lat()) 
         && !empty($eiLocation->get_lon()))
      {
        $body['lat'] = doubleval($eiLocation->get_lat());
        $body['lng'] = doubleval($eiLocation->get_lon());
      }
    }

    $tags = array();
    foreach($eiEvent->get_tags() as $eiTag)
    {
      array_push($tags, 
        $this->convert_to_kvm_tag($eiTag->get_name()));
    }
    foreach($eiEvent->get_categories() as $eiCat)
    {
      if( !in_array(
        $this->convert_to_kvm_tag(
          $eiCat->get_name()), $tags))
      {
        array_push($tags, 
          $this->convert_to_kvm_tag($eiCat->get_name()));
      }
    }

    $fixed_tag = $this->convert_to_kvm_tag(
                   get_option('kvm_fixed_tag'));
    if(!empty($fixed_tag))
    {
      array_push($tags, $fixed_tag);
    }

    $body['tags'] = $tags;

    return $body;
  }

  private function createEvent($body)
  {
    $eiEvent = new EICalendarEvent();
    
    if(array_key_exists('id', $body))
    {
      $eiEvent->set_uid( $body['id'] );
    }
    
    if(array_key_exists('title', $body))
    {
      $eiEvent->set_title( $body['title'] );
    }
    
    if(array_key_exists('description', $body))
    {
      $eiEvent->set_description( $body['description'] );
    }

    if(array_key_exists('start', $body))
    {
      $eiEvent->set_start_date( $body['start'] );
    }

    if(array_key_exists('end', $body))
    {
      $eiEvent->set_end_date( $body['end'] );
    }

    if(array_key_exists('organizer', $body))
    {
      $eiEvent->set_contact_name( $body['organizer'] );
    }

    if(array_key_exists('email', $body))
    {
      $eiEvent->set_contact_email( $body['email'] );
    }

    if(array_key_exists('telephone', $body))
    {
      $eiEvent->set_contact_phone( $body['telephone'] );
    }

    if(array_key_exists('website', $body))
    {
      $eiEvent->set_contact_website( $body['website'] );
    }
    // TODO: Fill elements füther
    return $eiEvent;
  }
}
