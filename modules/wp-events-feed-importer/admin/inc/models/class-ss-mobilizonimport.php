<?php
/**
  * Controller SSMobilizonImport
  * Control the import of Mobilizon feed
  *
  * @author     Sjoerd Takken
  * @copyright 	No Copyright.
  * @license   	GNU/GPLv2, see http://www.gnu.org/licenses/gpl-2.0.html
  * @link		    https://github.com/kartevonmorgen
  */
class SSMobilizonImport extends SSAbstractImport 
{
  private $data;
  private $response;

  private function get_data()
  {
    $url = $this->get_feed_url();
    $query = $this->set_up_event_list_query();
    if($this->is_echo_log())
    {
      echo '<p>Read Mobilizon Feed ' . $url . '</p>';
      echo '<p>GQLQuery: ' . $query . '</p>';
    }
    $this->response = $this->do_query($url, 
                                      $query);
    if ( wp_remote_retrieve_response_code( 
           $this->response ) != 200 ) 
    {
      return false;
    }
  
    $body = json_decode(
              wp_remote_retrieve_body( 
                $this->response ), 
              true);
    $this->data = $body['data'];
    return $this->data;
	}


	public function is_feed_valid()
  {
    return !empty($this->get_data());
  }

  public function read_feed_uuid()
  {
    return 'mobilizon_' . $this->get_feed_id();
  }

  public function read_feed_title()
  {
    $data = $this->get_data();
    if(empty($data))
    {
      set_error('get error for Mobilizon GraphQL request ' . $this->response);
      return null;
    }
    return $data['config']['name'];
  }

  public function read_events_from_feed()
  {
    $data = $this->get_data();
    if(empty($data))
    {
      return array();
    }

    // Extract the events as an array 
    // from the query's response body
    $events = $data['searchEvents']['elements'];

    $eiEvents = array();
    foreach($events as $event)
    {
      if($this->is_echo_log())
      {
        echo '<p>  read Mob Event  ' . $event['title'] . '</p>';
      }
      // Only import events in the Group
      if(!$event['local'])
      {
        if($this->is_echo_log())
        {
          echo '<p>  Mob Event is not local</p>';
        }
        continue;
      }

      // Only import events from filtered mobilizon groups 
      // declared in the Feed.
      if(!$this->is_in_mobgroups($event['attributedTo']['preferredUsername']))
      {
        if($this->is_echo_log())
        {
          echo '<p>  Mob Event is not in searched Groups</p>';
        }
        continue;
      }

      array_push($eiEvents, $this->read_event($event));
    }
    return $eiEvents;
  }

  /**
	 * Sets up a query to fetch an list of events from the 
   * set mobilizon server, not the details yet
	 *
	 * @since    1.0.0
	 * @access   private
   */
  private function set_up_event_list_query() 
  {
    $group_name = $this->get_feed_id();
    $date_now = date("c");
    $query = 'query  
                {
                  config
                  {
                    name
                  }
                  searchEvents(beginsOn:"' . $date_now . '")
                  {
                      elements 
                      {
                        uuid,
                        local,
                        updatedAt,
                        title,
                        attributedTo {
                          preferredUsername
                        }
                        url,
                        beginsOn,
                        endsOn,
                        description,
                        onlineAddress,
                        status,
                        visibility,
                        tags {
                          slug,
                          title
                        },
                        physicalAddress {
                          description,
                          street,
                          postalCode,
                          locality,
                          region,
                          country,
                          geom
                        }
                      }
                  }
                }';
    return $query;
  }

  /**
	 * Adds https:// protokoll if no url scheme is present
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    string     $baseURL    the domain/hostname/url of the mobilizon instance
   * @param    string     $query      the graphql query string see https://framagit.org/framasoft/mobilizon/-/blob/master/schema.graphql
   */
  private function do_query($baseURL, $query) 
  {
    // Get API-endpoint from Instance URL
    $baseURL = rtrim($baseURL, '/');
    $url_array = array($baseURL, "api");
    $endpoint = implode('/', $url_array);
    //$endpoint = $this->addhttp($endpoint);

    // Define default GraphQL headers
    $headers = ['Content-Type: application/json', 
                'User-Agent: Wordpress Mobilizon GraphQL client'];
    $body = array('query' => $query);
    $args = array(
      'body'    => $body,
      'headers' => $headers);

    // Send HTTP-Query and return the response
    return(wp_remote_post($endpoint, $args));
  }

  private function read_event($event) 
  {
    $eiEvent = new EiCalendarEvent();

    $uid = $event['uuid'];
    $startdate = $event['beginsOn'];
    $enddate = $event['endsOn'];

    $eiEvent->set_uid(sanitize_title($uid));
    $eiEvent->set_slug(sanitize_title($uid));
    $eiEvent->set_title($event['title']);
    $eiEvent->set_description($event['description']);
    $eiEvent->set_link($event['url']);

    $eiEvent->set_start_date($startdate);
    $eiEvent->set_end_date($enddate);
    $eiEvent->set_all_day(false);

    $eiEvent->set_published_date($event['updatedAt']);
    $eiEvent->set_updated_date($event['updatedAt']);

    foreach($event['tags'] as $tag)
    {
      $eiEvent->add_tag(new WPTag($tag['title']));
    }

    if($this->is_feed_include_mobgroup())
    {
      $eiEvent->add_tag(new WPTag($event['attributedTo']['preferredUsername']));
    }

    $wpLocH = new WPLocationHelper();
    $wpLocation = null;

    if(!empty($event['onlineAddress']))
    {
      $eiEvent->set_link($event['onlineAddress']);
    }
    else if(!empty($event['physicalAddress'])) 
    {
      $location = $event['physicalAddress'];
      $wpLocation = new WPLocation();
      $wpLocH->set_name( $wpLocation, 
                         $location['description'] );
      $wpLocH->set_swapped_address($wpLocation,  
                                   $location['street'] );
      $wpLocH->set_zip( $wpLocation, 
                        $location['postalCode'] );
      $wpLocH->set_city( $wpLocation, 
                         $location['locality'] );
      $wpLocH->set_state( $wpLocation, 
                          $location['region'] );
      $wpLocH->set_country( $wpLocation, 
        $location['country'] );
      if(!empty($location['geom']))
      {
        $geom = explode(';', $location['geom']);
        if(count($geom) > 1)
        {
          $wpLocation->set_lon($geom[0]);
          $wpLocation->set_lat($geom[1]);
        }
      }
    }

    if(!empty($wpLocation))
    {
      $eiEvent->set_location($wpLocation);
    }

    //$eiEvent->set_contact_name($vEvent->get_organizer_name());
    //$eiEvent->set_contact_email($vEvent->get_organizer_email());

    // In ICal we do categories as tags, weil tags do not exists
    // and we do not know them really.
    //foreach($vEvent->get_categories() as $cat)
    //{
    //  $eiEvent->add_tag(new WPTag($cat));
    //}
    return $eiEvent;
  }

  private function is_in_mobgroups($searched_group)
  {
    // If empty we import all events
    if( empty( $this->get_feed_filtered_mobgroups()))
    {
      return true;
    }

    $searched_group = trim($searched_group);
    
    foreach($this->get_feed_filtered_mobgroups() as $group )
    {
      $group = trim($group);
      $group = str_replace('@', '', $group);

      if( $searched_group === $group)
      {
        return true;
      }
    }
    return false;
  }

  public function get_feed_filtered_mobgroups()
  {
    $au = new PHPArrayUtil();

    $result = explode(',', 
      $this->get_feed_meta('ss_feed_filtered_mobgroups'));
    return $au->remove_empty_entries($result);
  }

  public function is_feed_include_mobgroup()
  {
    return $this->get_feed_meta('ss_feed_include_mobgroup');
  }
}
