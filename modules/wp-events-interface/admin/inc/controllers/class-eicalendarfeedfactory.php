<?php
if ( ! class_exists( 'EICalendarFeedFactory' ) ) 
{

/**
 * UICalendarFeedFactory
 * Check which different Event Calendar Plugins are available
 * and activated.
 * The Factory create instances for all the available and activated
 * Calendars.
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class EICalendarFeedFactory 
{
  private $supported_plugins = array();
  private $load_available_calendarfeeds = null;
  
  public function __construct()
  {
    $this->supported_plugins = array(
      'SimpleEvents',
      'Ai1ec',
      'EventsManager',
      'TheEventsCalendar');
    $this->create_available_calendar_feeds();
  }

  /**
   * Load the available calendar feeds
   *
   */
  private function create_available_calendar_feeds() 
  {
    $this->load_available_calendarfeeds = array();
	  foreach ( $this->supported_plugins as $plugin ) 
    {
      $class_name = "EICalendarFeed$plugin";
      if ( class_exists( $class_name ) ) 
      {
	      $feed = new $class_name;
        error_log( 'init feed ' . $class_name);
	      if ( $feed->is_feed_available() )
        {
          error_log( 'do init feed ' . $class_name);
          $feed->init();
	        array_push($this->load_available_calendarfeeds, $feed);
        }
		  }
    }
  }

  public function get_feeds() 
  {
    if( !isset( $this->load_available_calendarfeeds ))
    {
      $this->create_available_calendar_feeds();
    }
    return $this->load_available_calendarfeeds;
  }

  public function get_feed( $identifier ) 
  {
    foreach ( $this->get_feeds() as $feed ) 
    {
      if ( $identifier == $feed->get_identifier() )
      {
        return $feed;
      }
    }
    return null;
  }

}

}
