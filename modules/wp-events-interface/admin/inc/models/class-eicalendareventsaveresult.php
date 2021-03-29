<?php

if ( ! class_exists( 'EICalendarEventSaveResult' ) ) 
{

/**
  * EICalendarEventSaveResult
  * This result is used to return a report wenn an EICalendarEvent 
  * object is saved in the native Event Calendar.
  * If the save action is succesfull, then the event_id of the 
  * native Event Calendar will be returned, otherwise an error message
  * will be returned.
  * 
  * @author     Sjoerd Takken
  * @copyright  No Copyright.
  * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
  */
class EICalendarEventSaveResult 
{
	private $_error;
	private $_event_id;
	private $_post_id;

  public function __construct() 
  {
    $_error = null;
    $_event_id = 0;
    $_post_id = 0;
  }

  public function set_error( $error ) 
  {
    $this->_error = $error;
  }

  /**
   * Return an error string of an error is set
   *
   * @return String
   */
  public function get_error()
  {
    return $this->_error;
  }

  public function has_error()
  {
    return !empty( $this->_error );
  }

  public function set_event_id( $event_id ) 
  {
    $this->_event_id = $event_id;
  }

  /**
   * Return the event_id if the event is saved
   * succesfull.
   *
   * @return int
   */
  public function get_event_id()
  {
    return $this->_event_id;
  }

  public function set_post_id( $post_id ) 
  {
    $this->_post_id = $post_id;
  }

  /**
   * Return the post_id if the event is saved
   * succesfull.
   *
   * @return int
   */
  public function get_post_id()
  {
    return $this->_post_id;
  }
}

}
