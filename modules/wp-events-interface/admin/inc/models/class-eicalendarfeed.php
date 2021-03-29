<?php
if ( ! class_exists( 'EICalendarFeed' ) ) 
{

/**
  * EICalendarFeed
  * Read events from the native Event Calendar as an array
  * of EICalendarEvent objects.
  * Save EICalendarEvent objects into the native Event Calendar
  * The EICalendarEvent Object contains all the information
  * about an event. So the native implementation is hidden for 
  * the users of this Interface 
  *
  * @author     Sjoerd Takken
  * @copyright  No Copyright.
  * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
  */
abstract class EICalendarFeed 
{
  private $_eiEventSavedListeners = array();
  private $_suppress_event_saved = false;
  private $_eiEventDeletedListeners = array();

  protected $REPEAT_DAY;
  protected $REPEAT_WEEK;
  protected $REPEAT_MONTH;
  protected $REPEAT_YEAR;

  public function init()
  {

  }


  /**
   * Translate local feed frequency into
   * @param $frequency
   * @return string
   */
  protected function get_repeat_frequency_from_feed_frequency( $frequency ) 
  {
    switch ( $frequency ) 
    {
      case $this->REPEAT_DAY:
        return EICalendarEvent::REPEAT_DAY;
      case $this->REPEAT_WEEK:
        return EICalendarEvent::REPEAT_WEEK;
      case $this->REPEAT_MONTH:
        return EICalendarEvent::REPEAT_MONTH;
      case $this->REPEAT_YEAR:
        return EICalendarEvent::REPEAT_YEAR;
    }
    return false;
  }

  /** 
   * Add a listener when an event is saved.
   *
   * @param listener EIEventSavedListenerIF
   */
  public function add_event_saved_listener($listener)
  {
    array_push( $this->_eiEventSavedListeners, $listener );
  }

  /**
   * Return all the registered Listeners
   * @return EIEventSavedListenerIF[]
   */
  private function get_event_saved_listeners()
  {
    return $this->_eiEventSavedListeners;
  }

  protected function fire_event_saved($event_id)
  {
    if( $this->is_event_saved_suppressed() )
    {
      return;
    }

    $eiEvent = $this->get_event_by_event_id( $event_id );
    if(empty($eiEvent))
    {
      return;
    }
    foreach( $this->get_event_saved_listeners() as $listener )
    {
      $listener->event_saved($eiEvent);
    }
  }

  /**
   * Set suppressing the fire_event_saved(..) event on true or 
   * false
   *
   * @param $suppress_save_event boolean: if true, then the
   *                                      fire_event_saved(..) will not
   *                                      be executed. 
   */
  public function set_suppress_event_saved($suppress)
  {
    $this->_suppress_event_saved = $suppress;
  }

  /**
   * Check if it allowed to execute the fire_event_saved(..)
   * @return boolean
   */
  public function is_event_saved_suppressed()
  {
    return $this->_suppress_event_saved;
  }

  /** 
   * Add a listener when an event is delted.
   *
   * @param listener EIEventDeletedListenerIF
   */
  public function add_event_deleted_listener($listener)
  {
    array_push( $this->_eiEventDeletedListeners, $listener );
  }

  /**
   * Return all the registered Listeners
   * @return EIEventSavedListenerIF[]
   */
  private function get_event_deleted_listeners()
  {
    return $this->_eiEventDeletedListeners;
  }

  protected function fire_event_deleted($event_id)
  {
    $eiEvent = $this->get_event_by_event_id( $event_id );
    if(empty($eiEvent))
    {
      return;
    }
    foreach( $this->get_event_deleted_listeners() as $listener )
    {
      $listener->event_deleted($eiEvent);
    }
  }

  /**
   * Delete the underlying EICalendarEvent object 
   * for a determinated event_id.
   *
   * @param $event_id int: should be the eiEvent->get_event_id()
   */
  abstract function delete_event_by_event_id( $event_id );

  /**
   * Retrieve the EICalendarEvent object for a determinated
   * event_id.
   *
   * @param $event_id int: should be the eiEvent->get_event_id()
   * @return EICalendarEvent
   */
  abstract function get_event_by_event_id( $event_id );

  /**
   * Retrieve the EICalendarEvent object for a determinated
   * uid.
   *
   * @param $uid string: should be the eiEvent->get_uid()
   * @return EICalendarEvent
   */
  public function get_event_by_uid($uid)
  {
    return null;
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
  abstract function get_events( $start_date, $end_date, $event_cat );

  protected function fill_event_by_post($post, $eiEvent)
  {
    $eiEvent->set_post_id( $post->ID );
    $eiEvent->set_post_status( $post->post_status );
    $eiEvent->set_owner_user_id( $post->post_author );
    $eiEvent->set_title( 
      stripslashes_deep($post->post_title));
    $eiEvent->set_description( 
      stripslashes_deep( $post->post_content ));

    if( !empty($post->post_excerpt))
    {
      $eiEvent->set_excerpt( 
        stripslashes_deep( $post->post_excerpt ));
    }
    else
    {
      $eiEvent->set_excerpt(''); 
    }
  }

  /**
   * Save the EICalendarEvent object into the native Event Calendar
   *
   * @param $eiEvent EICalendarEvent
   * @return EICalendarEventSaveResult: Result of the saving action.
   */
  abstract function save_event( $eiEvent );

  /**
   * Sort events by the start date
   * @param $events EICalendarEvent[]
   * @return EICalendarEvent[]
   */
  function sort_events_by_start_date( $events ) 
  {
    usort( $events, array( $this, 'compare_event_start_date' ) );
    return $events;
  }

  /**
   * @param $a EICalendarEvent
   * @param $b EICalendarEvent
   *
   * @return int
   */
  function compare_event_start_date( $a, $b ) 
  {
    if ( $a->get_start_date() == $b->get_start_date() )
    {
      return 0;
    }
    return ( $a->get_start_date() < $b->get_start_date() ) ? -1 : 1;
  }

  public function register_for_kartevonmorgen()
  {
    $ui_metabox = new UIMetabox('kvm_metabox', 
                                'Karte von Morgen',
                                $this->get_posttype());
    $field = $ui_metabox->add_textfield('kvm_event_id', 
                                        'Id');
    $field->set_disabled(true);
    $field = $ui_metabox->add_textarea('event_kvm_log', 
                                       'Logging');
    $field->set_disabled(true);
    $ui_metabox->register();
  }

  /**
   * Fetch description for this calendar feed
   *
   * @return string
   */
  public abstract function get_description();

  /**
   * Fetch unique identifier for this calendar feed
   *
   * @return string
   */
  public abstract function get_identifier();

  /** 
   * Returns the posttype for the event
   */
  public abstract function get_posttype();

  /**
   * Checks of the native Calendar (its Wordpress Plugin)
   * is activated.
   * The implementation can use 
   * is_feed_available_for_plugin('plugin_dir/plugin_mainphpfile.php')
   * @return boolean
   */ 
	public abstract function is_feed_available();

  /**
   * Checks if the plugin is activated.
   *
   * @param plugin String: must be in the form like
   *                       'plugin_dir/plugin_mainphpfile.php'
   */
	public function is_feed_available_for_plugin($plugin)
  {
    return in_array( $plugin, 
                     (array) get_option( 'active_plugins', array() ) );
  }
}

}
