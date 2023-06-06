<?php

/**
 * OpeningHoursTimeRange  
 * according to https://wiki.openstreetmap.org/wiki/Key:opening_hours
 * not fully implemented
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class OpeningHoursTimeRange  
{
  private $_range;
  private $_index;

  public function __construct($range, $index)
  {
    $this->_range = $range;
    $this->_index = $index;
  }

  public function get_range()
  {
    return $this->_range;
  }

  public function get_index()
  {
    return $this->_index;
  }
  
  public function get_start_time()
  {
    $range = $this->get_range();
    $times = explode ("-", $range);
    if(empty($times))
    {
      return null;
    }

    return $times[0];
  }

  public function get_end_time()
  {
    $range = $this->get_range();
    $times = explode ("-", $range);
    if(empty($times))
    {
      return null;
    }

    if(count($times) < 2)
    {
      return null;
    }

    return $times[1];
  }

  public function is_default()
  {
    return $this->get_range() == '00:00-00:00';
  }

  public function after($timerange)
  {
    if($timerange == null)
    {
      return false;
    }

    $starttime_this = explode(':', $this->get_start_time());
    $starttime_that = explode(':', $timerange->get_start_time());

    if( intval($starttime_this[0]) > intval($starttime_that[0]) )
    {
      return true;
    }

    if( intval($starttime_this[0]) < intval($starttime_that[0]) )
    {
      return false;
    }

    if( intval($starttime_this[1]) > intval($starttime_that[1]) )
    {
      return true;
    }
    return false;
  }


  public function to_friendly_string()
  {
    $starttime = $this->get_start_time();
    if(empty($starttime))
    {
      return null;
    }
    $endtime = $this->get_end_time();
    if(empty($endtime))
    {
      return $starttime;
    }
    return $starttime . ' - ' . $endtime;
  }
}
