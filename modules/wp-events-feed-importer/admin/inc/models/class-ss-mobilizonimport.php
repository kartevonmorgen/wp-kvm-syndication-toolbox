<?php
/**
  * Controller SSMobilizonImport
  * Control the import of GraphQL Feed based on Mobilizon
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
      echo '<p>Read GraphQL (Mobilizon format) Feed ' . $url . '</p>';
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
    if(!array_key_exists('data', $body))
    {
      return null;
    }
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
      $this->set_error('get error for Mobilizon GraphQL request ' . 
                       print_r($this->response, true));
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

      // Only import events in the Group
      if(empty($event['attributedTo']))
      {
        if($this->is_echo_log())
        {
          echo '<p>  Mob Event is not on a Group, we only import events on Groups</p>';
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
    $cats = $this->get_feed_filtered_categories_str();
    if(empty($cats))
    {
      $searchEvents = 'searchEvents';
    }
    else
    {
      $searchEvents = 'searchEvents(category: "'.$cats.'")';
    }
    $group_name = $this->get_feed_id();
    $date_now = date("c");

    $extended1 = '';
    if($this->is_feed_extended_graphql())
    {
      $extended1 = '
                        excategories {
                          slug
                          title
                        }';
    }

    $query = 'query  
                {
                  config
                  {
                    name
                  }
                  ' . $searchEvents . '
                  {
                      elements 
                      {
                        uuid
                        local
                        updatedAt
                        title
                        attributedTo 
                        {
                          name
                          preferredUsername
                        }
                        options
                        {
                          isOnline
                        }
                        url
                        beginsOn
                        endsOn
                        description
                        onlineAddress
                        phoneAddress
                        status
                        tags 
                        {
                          slug
                          title
                        }' . $extended1 . '
                        physicalAddress 
                        {
                          description
                          street
                          postalCode
                          locality
                          region
                          country
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

    //$url_array = array($baseURL, "api");
    //$endpoint = implode('/', $url_array);
    
    $endpoint = $baseURL;

    // Define default GraphQL headers
    $headers = ['Content-Type' => 'application/json']; 

    $body = array('query' => $query);
    $body = wp_json_encode( $body );
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
    $eiEvent->set_contact_name($event['attributedTo']['name']);
    $eiEvent->set_contact_website($event['onlineAddress']);
    $eiEvent->set_contact_phone($event['phoneAddress']);

    $eiEvent->set_published_date($event['updatedAt']);
    $eiEvent->set_updated_date($event['updatedAt']);

    foreach($event['tags'] as $tag)
    {
      $eiEvent->add_tag(new WPTag($tag['title'], $tag['slug']));
    }

    // Extended GraphQL has categories
    // used in the Wordpress GraphQL Server Module
    if($this->is_feed_extended_graphql())
    {
      foreach($event['excategories'] as $cat)
      {
        $eiEvent->add_category(new WPCategory($cat['title'], $cat['slug']));
      }
    }

    if($this->is_feed_include_mobgroup())
    {
      if(!empty($event['attributedTo']['preferredUsername']))
      {
        $eiEvent->add_tag(
          new WPTag(
            $event['attributedTo']['preferredUsername']));
      }
    }

    $wpLocH = new WPLocationHelper();
    $wpLocation = null;

    if(!empty($event['options']['isOnline']))
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
    if(empty($this->get_feed_meta('ss_feed_include_mobgroup')))
    {
      return false;
    }
    return $this->get_feed_meta('ss_feed_include_mobgroup') == 'on';
  }

  public function is_feed_extended_graphql()
  {
    if(empty($this->get_feed_meta('ss_feed_extended_graphql')))
    {
      return false;
    }
    return $this->get_feed_meta('ss_feed_extended_graphql') == 'on';
  }

}
