<?php
if ( ! class_exists( 'EICalendarFeedSimpleEvents' ) ) 
{

/**
  * EICalendarFeedSimpleEvents
  * Events are saved in an own created post_type as EICalenderEvents 
  * The EICalendarEvent Object contains all the information
  * about an event.  
  *
  * @author     Sjoerd Takken
  * @copyright  No Copyright.
  * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
  */
class EICalendarFeedSimpleEvents extends EICalendarFeed 
{ 
  public function init()
  {
  }

  public function get_simple_events_module()
  {
    $mc = WPModuleConfiguration::get_instance();
    return $mc->get_module('wp-simple-events');
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

    if ( ! has_action( 'save_post_simple_event', 
             array( $this, 'event_saved' ) ))
    {
      add_action( 'save_post_simple_event', 
                         array( $this, 'event_saved') , 
                         12, 
                         3 );
    }
  }

  /** 
   * Add a listener when an event is deleted.
   * Siehe https://wp-events-plugin.com/
   *               tutorials/saving-custom-event-information/
   *
   * @param listener EIEventDeletedListenerIF
   */
  public function add_event_deleted_listener($listener)
  {
    parent::add_event_deleted_listener($listener);

    if ( !has_filter( 'trashed_post', array( $this, 'event_deleted' ) ))
    {
      add_action('trashed_post', array( $this, 'event_trashed' ) );
      add_action('deleted_post', array( $this, 'event_trashed' ) );
    }
  }

  function event_saved($event_id, 
                       $event, 
                       $update = false) 
  {
    $this->fire_event_saved($event_id);
  }

  function event_trashed($post_id)
  {
    $this->fire_event_deleted($post_id);
  }

  public function get_event_by_event_id( $event_id ) 
  {
    if(empty($event_id))
    {
      return null;
    }
    return $this->convert_to_eievent( get_post($event_id));
  }

  public function delete_event_by_event_id( $event_id )
  {
    wp_delete_post( $event_id, true );
  }

  public function get_event_by_uid($uid)
  {
    $module = $this->get_simple_events_module(); 

    $args = array(
      'name'        => $uid,
      'post_type'   => $module->get_posttype(),
      'post_status' => array('draft', 'pending', 'publish'),
      'numberposts' => 1);
    
    $events = get_posts( $args );
    if(empty($events))
    {
      return null;
    }
    return reset($posts);
  }

  /**
   * Retrieve the EICalendarEvent objects for a determinated
   * Time range.
   *
   * @param $start_date int
   * @param $end_date int
   * @param $event_cat String: is the slug of the Event Category
   * @return EICalendarEvent[]
   */
  public function get_events( $start_date, $end_date, $event_cat=NULL ) 
  {
    $result = array();
    $module = $this->get_simple_events_module(); 
    $posts = $module->get_events($start_date, $end_date);
    foreach($posts as $post)
    {
      array_push($result, $this->convert_to_eievent($post));
    }
    return $result;
  }

  private function convert_to_eievent($post)
  {
    $posttype = $post->post_type;
    $eiEvent = new EICalendarEvent();
    $eiEvent->set_slug( $post->post_name );
    $eiEvent->set_link( get_permalink( $post ));

    $this->fill_event_by_post($post, $eiEvent);

	  $eiEvent->set_plugin( $this->get_identifier() );

    $eiEvent->set_published_date( $post->post_date );
    $eiEvent->set_updated_date( $post->post_modified );

    $helper = new WPMetaFieldsHelper($post->ID);
    $helper->set_prefix($posttype);
    $helper->add_field('_start_date');
    $helper->add_field('_end_date');
    $helper->add_field('_all_day');
    $helper->add_field('_contact_name');
    $helper->add_field('_contact_email');
    $helper->add_field('_contact_phone');
    $helper->add_field('_contact_website');

    $eiEvent->set_start_date($helper->get_value('_start_date'));
    $eiEvent->set_end_date($helper->get_value('_end_date'));
    $eiEvent->set_all_day($helper->get_value('_all_day'));

    $eiEvent->set_location( $this->get_ei_event_location($post));

    $value = $helper->get_value('_contact_name');
    if( ! empty( $value))
    {
      $eiEvent->set_contact_name( $value );
    }

    // Email
    $value = $helper->get_value('_contact_email');
    if( ! empty( $value))
    {
      $eiEvent->set_contact_email( $value );
    }

    // Telefon
    $value = $helper->get_value('_contact_phone');
    if( ! empty( $value))
    {
      $eiEvent->set_contact_phone( $value );
    }

    // Website
    $value = $helper->get_value('_contact_website');
    if( ! empty( $value))
    {
      $eiEvent->set_contact_website( $value );
    }


    $post_categories = wp_get_post_categories( $post->ID, 
                                               array( 'fields' => 'all' ) );
		$eiEvent->set_categories( 
      WPCategory::create_categories($post_categories));

    $post_tags = wp_get_post_tags( $post->ID );
    $eiEvent->set_tags( WPTag::create_tags($post_tags));

    return $eiEvent;
  }

  private function get_ei_event_location($post)
  {
    $posttype = $post->post_type;
    $helper = new WPMetaFieldsHelper($post->ID);
    $helper->set_prefix($posttype . '_location_');

    $helper->add_field('name');
    $helper->add_field('address');
    $helper->add_field('zip');
    $helper->add_field('city');
    $helper->add_field('country');

    $eiEventLocation = new WPLocation();
    $wpLocH = new WPLocationHelper();
    $wpLocH->set_name($eiEventLocation, 
                      $helper->get_value('name'));

    $wpLocH->set_address( $eiEventLocation, 
                          $helper->get_value('address' ));
    $wpLocH->set_city( $eiEventLocation, 
                       $helper->get_value('city' ));
    $wpLocH->set_zip( $eiEventLocation, 
                      $helper->get_value('zip' ));
    $wpLocH->set_country_code( $eiEventLocation, 
                               $helper->get_value('country' ));
    $eiEventLocation->set_lon( $helper->get_value('lon' ));
    $eiEventLocation->set_lat( $helper->get_value('lat' ));

    if($wpLocH->is_location_empty($eiEventLocation))
    {
      return null;
    }
    return $eiEventLocation;
  }

  /**
   * Save the EICalendarEvent object into ...
   *
   * @param $eiEvent EICalendarEvent
   * @return EICalendarEventSaveResult: Result of the saving action.
   */
  public function save_event($eiEvent)
  {
    $module = $this->get_simple_events_module(); 
    $post = $module->get_event_by_slug($eiEvent->get_uid());

    $postarr = array();
    if(!empty($post))
    {
      $post_id =  $post->ID;
      $postarr = array('ID' => $post_id,
                       'post_author' => $eiEvent->get_owner_user_id());
    }
    $postarr = $this->fill_postarr($postarr, $eiEvent);

    if(empty($post))
    {
      $post_id = wp_insert_post($postarr);
    }
    else
    {
      wp_update_post($postarr);
    }
    
    $this->save_event_categories($post_id, $eiEvent);
    $this->save_event_tags($post_id, $eiEvent);

    $result = new EICalendarEventSaveResult();
    $result->set_event_id( $post_id );
    $result->set_post_id( $post_id );
    return $result;
  }

  private function fill_postarr($postarr, $eiEvent)
  {
    global $allowedposttags;
    $module = $this->get_simple_events_module(); 
    $location = $eiEvent->get_location();

    $postarr['post_type'] = $module->get_posttype();
    $postarr['post_title'] = $eiEvent->get_title();
    $postarr['post_status'] = $eiEvent->get_post_status();
    $postarr['post_content'] = wp_kses( wp_unslash($eiEvent->get_description()), $allowedposttags);
    $meta = array(
        'simple_event_start_date' => $eiEvent->get_start_date_unixtime(),
        'simple_event_end_date' => $eiEvent->get_end_date_unixtime(),
        'simple_event_all_day' => $eiEvent->get_all_day(),
        'simple_event_contact_name' => $eiEvent->get_contact_name(),
        'simple_event_contact_email' => $eiEvent->get_contact_email(),
        'simple_event_contact_website' => $eiEvent->get_contact_website(),
        'simple_event_contact_phone' => $eiEvent->get_contact_phone(),
    );

    if(!empty($location))
    {
      $meta['simple_event_location_name'] = $location->get_name();
      $meta['simple_event_location_address'] = $location->get_street() . 
            ' ' . $location->get_streetnumber();
      $meta['simple_event_location_zip'] = $location->get_zip();
      $meta['simple_event_location_country'] = $location->get_country_code();
      $meta['simple_event_location_lat'] = $location->get_lat();
      $meta['simple_event_location_lon'] = $location->get_lon();
    }

    $postarr['meta_input'] = $meta;
    return $postarr;

  }

  private function save_event_categories($post_id, $eiEvent)
  {
    $term_cat_ids = array();
    foreach( $eiEvent->get_categories() as $cat )
    {
      $cat_term = get_term_by( 'slug', 
                               $cat->get_slug(), 
                               'category' );
      if ( empty ( $cat_term ))
      {
        $term_array = wp_insert_term( $cat->get_name(), 
                                      'category',
                                      array( 'slug' => $cat->get_slug(),
                                             'name' => $cat->get_name() ));
        if ( intval( $term_array['term_id'] ) > 0 )
        {
          array_push( $term_cat_ids, intval( $term_array['term_id'] ));
        }
      }
      else
      {
        if ( intval( $cat_term->term_id ) > 0 )
        {
          array_push( $term_cat_ids, intval( $cat_term->term_id ) );
        }
      }
    }
    wp_set_post_categories($post_id, $term_cat_ids);

  }

  private function save_event_tags($post_id, $eiEvent)
  {

    $term_tag_ids = array();
    foreach( $eiEvent->get_tags() as $tag )
    {
      $tag_term = get_term_by( 'slug', 
                                    $tag->get_slug(), 
                                    'post_tag' );
      if ( empty ( $tag_term ))
      {
        $term_array = wp_insert_term( $tag->get_name(), 
                                      'post_tag',
                                   array( 'slug' => $tag->get_slug(),
                                          'name' => $tag->get_name() ));
        if ( intval( $term_array['term_id'] ) > 0 )
        {
          array_push( $term_tag_ids, intval( $term_array['term_id'] ));
        }
      }
      else
      {
        if ( intval( $tag_term->term_id ) > 0 )
        {
          array_push( $term_tag_ids, intval( $tag_term->term_id ) );
        }
      }
    }

    wp_set_post_tags($post_id, $term_tag_ids);
  }

  public function get_description() 
  {
    return 'Simple Events';
  }

  public function get_identifier() 
  {
    return 'simple-events';
  }

  public function get_posttype() 
  {
    $module = $this->get_simple_events_module(); 
    return $module->get_posttype();
  }


  public function is_feed_available() 
  {
    $mc = WPModuleConfiguration::get_instance();
    return $mc->is_module_enabled('wp-simple-events');
  }

}

}
