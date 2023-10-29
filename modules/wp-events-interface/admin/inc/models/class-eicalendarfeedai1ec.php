<?php
if ( ! class_exists( 'EICalendarFeedAi1ec' ) ) {

/**
  * EICalendarFeedAi1ec
  * Read events from the All in One Event Calendar as an array
  * of EICalendarEvent objects.
  * Save EICalendarEvent objects into the All in One Event Calendar
  * The EICalendarEvent Object contains all the information
  * about an event. So the 
  *
  * @author     Sjoerd Takken
  * @copyright  No Copyright.
  * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
  */
class EICalendarFeedAi1ec extends EICalendarFeed 
{
  /** 
   * Add a listener when an event is saved.
   *
   * @param listener EIEventSavedListenerIF
   */
  public function add_event_saved_listener($listener)
  {
    parent::add_event_saved_listener($listener);

    if ( ! has_action( 'ai1ec_event_saved', 
             array( $this, 'ai1ec_event_saved' ) ))
    {
      add_filter( 'ai1ec_event_saved', 
        array( $this, 'ai1ec_event_saved' ), 10, 3 );
    }
  }

  public function ai1ec_event_saved($post_id, $event, $update)
  {
    $this->fire_event_saved($post_id);
  }

  /** 
   * Add a listener when an event is deleted.
   *
   * @param listener EIEventDeletedListenerIF
   */
  public function add_event_deleted_listener($listener)
  {
    parent::add_event_deleted_listener($listener);

    if ( !has_action( 'trashed_post', array( $this, 'aio_event_deleted' ) ))
    {
      add_action('trashed_post', array( $this, 'aio_event_trashed' ) );
    }
  }

  public function aio_event_trashed($post_id)
  {
    $event = get_post($post_id);
    if(empty($event))
    {
      // It is not an Event
      return;
    }
    $this->fire_event_deleted($event_id);
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
   * event_id -> post_id.
   *
   * @param $event_id int: is post_id
   * @return EICalendarEvent
   */
  public function get_event_by_event_id( $event_id ) 
  {
    $ai1ec_registry = apply_filters( 'ai1ec_registry', false );
    if(!$ai1ec_registry)
    {
      return null;
    }

	  $search = $ai1ec_registry->get( 'model.search' );
    $event = $search->get_event($event_id);
	  $post = get_post( $event->get( 'post_id' ) );
    
    $eiEvent = $this->convert_to_eievent($post, $event);

    return $eiEvent;
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
    $retval = array();

    $ai1ec_registry = apply_filters( 'ai1ec_registry', false );
    if(!$ai1ec_registry)
    {
      return $retval;
    }

	  $start_time = $ai1ec_registry->get( 'date.time' );
	  $end_time = $ai1ec_registry->get( 'date.time' );
	  $search = $ai1ec_registry->get( 'model.search' );
	  $settings = $ai1ec_registry->get( 'model.settings' );

	  // Get localized time
	  $start_time->set_date_time( date( 'Y-m-d H:m:s', $start_date ), get_option( 'timezone_string' ) );
	  $end_time->set_date_time( date( 'Y-m-d H:m:s', $end_date ), get_option( 'timezone_string' ) );

	  $filters = array(
		  'cat_ids' => array(),
		  'tag_ids' => array(),
	    );
      
    $category_invalid = false;
    if ( !empty($event_cat))
    {
      $category_term = get_term_by( 'slug', $event_cat, 'events_categories' );
      if ( $category_term !== NULL && intval( $category_term->term_id ) > 0 )
      { 
        $filters['cat_ids'] = array( $category_term->term_id);
      }
      else
      {
        $category_invalid = true;
      }
    }

    if($category_invalid)
    {
      $event_results = array();
    }
    else
    {
	    $event_results = $search->get_events_between( $start_time, 
                                                    $end_time, 
                                                    $filters );
    }

	  // see app/model/event/entity.php for properties (private vars without initial $_)
	  $post_ids = array();

    foreach ( $event_results as $event ) 
    {
	    $post = get_post( $event->get( 'post_id' ) );

	    if ( apply_filters( 'ei_ai1ec_recurring_once', false ) and in_array( $post->ID, $post_ids ) )
      {
		    continue;
      }

	    array_push( $post_ids, $post->ID);

      $eiEvent = $this->convert_to_eievent($post, $event);
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
   * Converts the All In One Event Calendar Event Type and Post
   * into an EICalendarEvent.
   *
   * @param $post array: contains the Post Object of the Event
   * @param $event : contains the native Object of teh All In One
   *                 Event Calendar
   * @return EICalendarEvent
   */
  private function convert_to_eievent($post, $event)
  {
    $image_src = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'medium' );
    if ( !empty( $image_src ) )
    {
      $image_url = $image_src[0];
    }
    else
    {
	    $image_url = false;
    }

	  $instance_id = $event->get( 'instance_id' );
	  $permalink = get_the_permalink( $post->ID );
	  if ( $instance_id )
    {
      $permalink .= ( ( false !== strpos( $permalink, '?' ) ) ? '&instance_id=' : '?instance_id=' ) . intval( $instance_id );
    }

    $wpLocH = new WPLocationHelper();

    $address = $event->get('address');
    $eiLoc = $wpLocH->create_from_freetextformat_local($address);
    $wpLocH->set_name( $eiLoc, $event->get('venue'));
    $wpLocH->set_state( $eiLoc, $event->get('province'));
    $wpLocH->set_country_code( $eiLoc, $event->get('country'));

    $eiEvent = new EICalendarEvent();
    $this->fill_event_by_post($post, $eiEvent);
    $eiEvent->set_link(  $permalink );
		$eiEvent->set_event_id( $post->ID );
		$eiEvent->set_event_instance_id( $instance_id );
    $eiEvent->set_start_date( $event->get('start')->format( 'Y-m-dTH:i:s', 
                                         $event->get( 'timezone_name' ) ));
    $eiEvent->set_end_date( $event->get( 'end' )->format( 'Y-m-dTH:i:s', 
                                         $event->get( 'timezone_name' ) ));
    $eiEvent->set_all_day( $event->get( 'allday' ));
		$eiEvent->set_published_date( get_the_date('Y-m-d H:i:s', $post->ID ));
		$eiEvent->set_updated_date( get_the_modified_date( 'Y-m-dTH:i:s', 
                                                       $post->ID ));
    $term_cats = get_the_terms( $post->ID, 'events_categories' );
		$eiEvent->set_categories( 
      WPCategory::create_categories($term_cats));

    $term_tags = get_the_terms( $post->ID, 'events_tags' );
    $eiEvent->set_tags( WPTag::create_tags($term_tags));

    if($wpLocH->is_valid($eiLoc))
    {
      $eiEvent->set_location( $eiLoc);
    }

    $eiEvent->set_contact_name( $event->get( 'contact_name' ));
    $eiEvent->set_contact_email( $event->get( 'contact_email' ));
    $eiEvent->set_contact_website( $event->get( 'contact_url' ));
    $eiEvent->set_contact_phone( $event->get( 'contact_phone' ));

    $eiEvent->set_event_website( $event->get( 'ticket_url' ));
    $eiEvent->set_event_image_url( $image_url );
    $eiEvent->set_event_cost( $event->get( 'is_free' ) ? __( 'FREE', 'events-interface' ) : $event->get( 'cost' ) );

    // REPEAT NOT YET IMPLEMENTED 
    //$eiEvent->set_repeat_frequency( $event->repeat_freq);
    //$eiEvent->set_repeat_interval( $this->get_repeat_frequency_from_feed_frequency( $event->repeat_int ));
    //$eiEvent->set_repeat_end( $event->repeat_end );

    // NOT USED
		//$eiEvent->set_plugin( xx );
    //$eiEvent->set_location_website( xx );
		//$eiEvent->set_location_phone( xx );
    //$eiEvent->set_contact_info( xx );
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
    return 'All-in-One Event Calendar';
  }

  public function get_identifier() 
  {
    return 'all-in-one-event-calendar';
  }

  public function get_posttype()
  {
    if(defined('AI1EC_POST_TYPE'))
    {
      return AI1EC_POST_TYPE;
    }
    return 'ai1ec_event';
  }

  public function is_feed_available() 
  {
    return self::is_feed_available_for_plugin( 'all-in-one-event-calendar/all-in-one-event-calendar.php' );
  }
}

}
