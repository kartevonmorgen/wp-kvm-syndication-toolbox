<?php

/**
 * OpeningHours for saving and  
 * Cronjobs
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class OpeningHoursDayType  
{
  private $_id;
  private $_title;
  private $_previous;
  private $_next;

  public function __construct($id, $title)
  {
    $this->_id = $id;
    $this->_title = $title;
  }

  public function set_previous($previous)
  {
    $this->_previous = $previous;
  }

  public function set_next($next)
  {
    $this->_next = $next;
  }

  public function get_id()
  {
    return $this->_id;
  }

  public function get_title()
  {
    return $this->_title;
  }

  public function get_previous()
  {
    return $this->_previous;
  }

  public function get_next()
  {
    return $this->_next;
  }

  public function is_monday()
  {
    return $this->get_id() == 'Mo';
  }

  public function is_sunday()
  {
    return $this->get_id() == 'Su';
  }
}
