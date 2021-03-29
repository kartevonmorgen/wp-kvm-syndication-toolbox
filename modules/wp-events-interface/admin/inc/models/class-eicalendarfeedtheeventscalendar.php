<?php
if ( ! class_exists( 'EICalendarFeedTheEventsCalendar' ) ) {

/**
  * EICalendarFeedTheEventsCalendar
  * Read events from the The Events Calendar as an array
  * of EICalendarEvent objects.
  * Save EICalendarEvent objects into the All in One Event Calendar
  * The EICalendarEvent Object contains all the information
  * about an event. So the 
  *
  * @author     Sjoerd Takken
  * @copyright  No Copyright.
  * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
  */
class EICalendarFeedTheEventsCalendar extends EICalendarFeed 
{
  public function init()
  {
    if ( !has_filter( 'tribe_events_tribe_venue_update', 
          array( $this, 'before_update_venue' )))
    {
      add_filter( 'tribe_events_tribe_venue_update', 
                  array( $this, 'before_update_venue'), 10,3 );
    }
  }

  public function before_update_venue($value, $venue_id, $data)
  {
    if(!get_option('ei_fill_lanlon_coordinates_over_osm', 
                   false))
    {
      return;
    }

    $wpLocation = $this->get_wp_location($venue_id); 
    if(empty($wpLocation))
    {
      return;
    }

    $wpLocationHelper = new WPLocationHelper();
    $wpLocation = 
      $wpLocationHelper->fill_by_osm_nominatim(
        $wpLocation);

    $this->fill_tribe_location($venue_id, $wpLocation);

    return $value;
  }

  private function fill_tribe_location($venue_id, 
                                       $wpLocation)
  {
    $wpLocationHelper = new WPLocationHelper();
    $address = $wpLocationHelper->get_address(
                                    $wpLocation);
    if(!empty($address))
    {
      update_post_meta( $venue_id, 
                        '_VenueAddress', 
                        $address );
    }

    if(!empty($wpLocation->get_zip()))
    {
      update_post_meta( $venue_id, 
                        '_VenueZip', 
                        $wpLocation->get_zip() );
    }

    if(!empty($wpLocation->get_city()))
    {
      update_post_meta( $venue_id, 
                        '_VenueCity', 
                        $wpLocation->get_city() );
    }

    // Coordinates are only supported
    // in The Events Calender PRO, so we use the
    // the same META fields as in PRO.
    if(!empty($wpLocation->get_lat()))
    {
      update_post_meta( $venue_id, 
                        '_VenueLat', 
                        $wpLocation->get_lat() );
    }

    if(!empty($wpLocation->get_lon()))
    {
      update_post_meta( $venue_id, 
                        '_VenueLng', 
                        $wpLocation->get_lon() );
    }
  }

  /** 
   * Add a listener when an event is saved.
   * Siehe https://wp-events-plugin.com/
   *               tutorials/saving-custom-event-information/
   *
   * @param listener EIEventSavedListenerIF
   */
  public function add_event_saved_listener($listener)
  {
    parent::add_event_saved_listener($listener);

    add_action( 'tribe_events_update_meta', 
      array( $this, 'tribe_event_saved' ), 10, 3 );
  }

  public function tribe_event_saved($event_id, $data, $event)
  {
    $this->fire_event_saved($event_id);
  }

  /**
   * Delete the underlying EICalendarEvent object 
   * for a determinated event_id.
   * NOT IMPLEMENTED YET
   *
   * @param $event_id int
   */
  public function delete_event_by_event_id( $event_id )
  {
  }

  /**
   * Retrieve the EICalendarEvent object for a determinated
   * event_id.
   * NOT IMPLEMENTED YET
   *
   * @param $event_id int
   * @return EICalendarEvent
   */
  public function get_event_by_event_id( $event_id ) 
  {
    $event_post = tribe_get_event( $event_id );
    return $this->convert_to_eievent($event_post);
  }

  /**
   * Retrieve the EICalendarEvent objects for a determinated
   * Time range.
   *
   * @param $start_date int: Time from Januar 1 1970 00:00:00 GMT in seconds
   * @param $end_date int: Time from Januar 1 1970 00:00:00 GMT in seconds
   * @param $event_cat String: is the slug of the Event Category
   * @return EICalendarEvent[]
   */
  public function get_events( $start_date, $end_date, $event_cat=NULL) 
  {
    $start_date_str = date(Tribe__Date_Utils::DBDATETIMEFORMAT, 
                           $start_date);
    $end_date_str = date(Tribe__Date_Utils::DBDATETIMEFORMAT, 
                         $end_date);

    $args = array('posts_per_page' => -1,
                  'start_date' => $start_date_str,
                  'end_date' => $end_date_str
                  );
                  

    $event_results = tribe_get_events( $args );

    foreach ( $event_results as $event_post ) 
    {
      $eiEvent = $this->convert_to_eievent($event_post);
      if( empty($eiEvent) )
      {
        continue;
      }
      $retval[] = $eiEvent;
    }
    $retval = $this->sort_events_by_start_date( $retval );
    return $retval;
  }

  /**
   * id can ben vanue id or post id
   */
  private function get_wp_location($id)
  {
    // Set Location
    $wpLocH = new WPLocationHelper();
    $eiLoc = new WPLocation();

    $venue_id = tribe_get_venue_id($id);
    $wpLocH->set_name( $eiLoc, 
                       tribe_get_venue( $venue_id ));
    $wpLocH->set_address( $eiLoc, tribe_get_address($venue_id));
    $wpLocH->set_zip( $eiLoc, tribe_get_zip($venue_id));
    $wpLocH->set_city( $eiLoc, tribe_get_city($venue_id));
    $wpLocH->set_country( $eiLoc, tribe_get_country($venue_id));
    $eiLoc->set_website( 
      tribe_get_venue_website_link( $venue_id ));
    $eiLoc->set_phone( 
      tribe_get_phone( $venue_id ));
    $eiLoc->set_lat( (float)
      get_post_meta( $venue_id, 
                     '_VenueLat', 
                     true ));
    $eiLoc->set_lon( (float)
      get_post_meta( $venue_id, 
                     '_VenueLng', 
                     true ));

    if($wpLocH->is_valid($eiLoc))
    {
      return $eiLoc;
    }
    return null;
  }

  /**
   * Converts the All In One Event Calendar Event Type and Post
   * into an EICalendarEvent.
   *
   * @param $post array: contains the Post Object of the Event
   * @param $event : contains the native Object of teh All In One
   *                 Event Calendar
   * @return EICalendarEvent
   */
  private function convert_to_eievent($post)
  {
    $image_src = wp_get_attachment_image_src( 
      get_post_thumbnail_id( $post->ID ), 'medium' );
    if ( !empty( $image_src ) )
    {
      $image_url = $image_src[0];
    }
    else
    {
	    $image_url = false;
    }



    $eiEvent = new EICalendarEvent();
    $wpLoc = $this->get_wp_location( $post->ID );
    if(!empty($wpLoc))
    {
      $eiEvent->set_location( $wpLoc );
    }

    $this->fill_event_by_post($post, $eiEvent);

	  $permalink = get_the_permalink( $post->ID );
    $eiEvent->set_link(  $permalink );
		
    $eiEvent->set_event_id( $post->ID );
    $eiEvent->set_start_date( tribe_get_start_date( $post, true, 'Y-m-d H:i:s' ));
    $eiEvent->set_end_date( tribe_get_end_date( $post, true, 'Y-m-d H:i:s' ));


		$eiEvent->set_published_date( get_the_date('Y-m-d H:i:s', $post->ID ));
		$eiEvent->set_updated_date( 
      get_the_modified_date( 'Y-m-d H:i:s', $post->ID ));

    // TAXONOMY = 'tribe_events_cat'
    $term_cats = get_the_terms( $post->ID, 
                                Tribe__Events__Main::TAXONOMY );
		$eiEvent->set_categories( 
      WPCategory::create_categories($term_cats));

    $term_tags = get_the_terms( $post->ID, 'post_tag' );
    $eiEvent->set_tags( WPTag::create_tags($term_tags));

    $eiEvent->set_contact_name(  
      tribe_get_organizer( $post->ID ));
    $eiEvent->set_contact_email( 
      tribe_get_organizer_email( $post->ID));
    $eiEvent->set_contact_website( 
      tribe_get_organizer_website_link( $post->ID ));
    $eiEvent->set_contact_phone( 
      tribe_get_organizer_phone( $post->ID ));

    $eiEvent->set_event_website( 
      tribe_get_event_website_url( $post->ID ));
    $eiEvent->set_event_image_url( $image_url );
    $eiEvent->set_event_cost( tribe_get_cost( $post->ID ));

    // REPEAT NOT YET IMPLEMENTED 
    //$eiEvent->set_repeat_frequency( $event->repeat_freq);
    //$eiEvent->set_repeat_interval( $this->get_repeat_frequency_from_feed_frequency( $event->repeat_int ));
    //$eiEvent->set_repeat_end( $event->repeat_end );

    // NOT USED
		//$eiEvent->set_plugin( xx );
		//$eiEvent->set_event_image_alt( xx );
    //$eiEvent->set_recurrence_text( xx );
		//$eiEvent->set_colour( xx );
		//$eiEvent->set_featured( xx );
    //$eiEvent->set_subtitle( xx );

    return $eiEvent;
  }

  /**
   * Save the EICalendarEvent object into the All in One Event Calendar
   * NOT IMPLEMENTED YET
   *
   * @param $eiEvent EICalendarEvent
   * @return EICalendarEventSaveResult: Result of the saving action.
   */
  public function save_event($eiEvent)
  {
    // NOT IMPLEMENTED YET
  }

  public function get_description() 
  {
    return 'The Events Calendar';
  }

  public function get_identifier() 
  {
    return 'the-events-calendar';
  }

  public function get_posttype()
  {
    return Tribe__Events__Main::POSTTYPE;
  }

  public function is_feed_available() 
  {
    return self::is_feed_available_for_plugin( 'the-events-calendar/the-events-calendar.php' );
  }
}

}
