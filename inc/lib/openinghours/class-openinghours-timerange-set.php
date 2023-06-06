<?php

/**
 * OpeningHoursTimeRangeSet  
 * according to https://wiki.openstreetmap.org/wiki/Key:opening_hours
 * not fully implemented
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class OpeningHoursTimeRangeSet  
{
  private $_timeranges;
  private $_days = array();

  public function __construct($timeranges)
  {
    $this->_timeranges = $timeranges;
  }

  public function get_timeranges()
  {
    return $this->_timeranges;
  }

  public function get_timeranges_str()
  {
    $results = array();
    foreach( $this->get_timeranges() as $timerange)
    {
      array_push( $results, $timerange->get_range() );
    }
    return implode(',', $results);
  }

  public function add_day($day)
  {
    array_push($this->_days, $day);
  }

  public function reorder_days()
  {
    $days = $this->get_days();
    $day = reset($days);
    $day_type = $day->get_day_type();
    if(!$day_type->is_monday())
    {
      return;
    }

    $monday = $day;

    $rdays = array_reverse($days);
    $rday = reset($rdays);
    $day_type = $rday->get_day_type();
    if(!$day_type->is_sunday())
    {
      return;
    }

    $newdays_first = array();
    $newdays_last = array();

    $next_day = $monday;
    $gapfound = 0;
    foreach($rdays as $day)
    {
      $day_type = $day->get_day_type();
      $next_day_type = $next_day->get_day_type();

      if($day_type->get_next() != $next_day_type)
      {
        $gapfound = $gapfound + 1;
        if($gapfound == 2)
        {
          return;
        }
      }
      
      if($gapfound == 0)
      {
        array_splice($newdays_first, 0,0, array($day));
      }
      if($gapfound == 1)
      {
        array_splice($newdays_last, 0,0, array($day));
      }
      $next_day = $day;
    }

    $days = array();
    foreach($newdays_first as $day)
    {
      $day_type = $day->get_day_type();
      array_push($days, $day);
    }
    foreach($newdays_last as $day)
    {
      $day_type = $day->get_day_type();
      array_push($days, $day);
    }
    $this->_days = $days;
    return $days;
  }

  public function get_days()
  {
    return $this->_days;
  }

  public function get_first_day()
  {
    if($this->is_empty())
    {
      return null;
    }
    return reset($this->_days);
  }

  public function get_last_day()
  {
    if($this->is_empty())
    {
      return null;
    }
    return end($this->_days);
  }

  public function is_empty()
  {
    return empty($this->get_days()) || count($this->get_days()) == 0;
  }

  public function has_one_day()
  {
    return !empty($this->get_days()) && count($this->get_days()) == 1;
  }

  public function has_more_days()
  {
    return !empty($this->get_days()) && count($this->get_days()) > 1;
  }

  /** 
   * Check if the days in the set are following
   * each other. if there are gaps we have to
   * create the key another way.
   */
  public function is_sequence()
  {
    $previous_day_type = null;
    foreach($this->get_days() as $day)
    {
      // In the first loop we should
      // set $previous_day_type otherwise
      // there is nothing to check
      $day_type = $day->get_day_type();
      if($previous_day_type == null)
      {
        $previous_day_type = $day_type;
        continue;
      }

      // If the Next Day is not the next day in this
      // set it is not a sequence
      if($previous_day_type->get_next() != $day_type)
      {
        return false;
      }
      $previous_day_type = $day_type;
    }
    return true;
  }

  public function has_same_timeranges($day)
  {
    return $this->get_timeranges_str() == $day->get_timeranges_str();
  }

  public function get_unique_key()
  {
    if($this->is_empty())
    {
      return '';
    }

    if($this->has_one_day())
    {
      $day = $this->get_first_day();
      $key = $day->get_day_type_id();
      $key .= ' ';
      $key .= $this->get_timeranges_str();
      return $key;
    }

    // So we have more then one day
    // Check if it is a sequence, then we have
    // another output of the key
    if($this->is_sequence())
    {
      $day = $this->get_first_day();
      $key = $day->get_day_type_id();
        
      $key .= '-';
          
      $day = $this->get_last_day();
      $key .= $day->get_day_type_id();
      $key .= ' ';
      $key .= $this->get_timeranges_str();
      return $key;
    }

    // So no Sequence, output Komma Layout
    $key = null;
    foreach($this->get_days() as $day)
    {
      if($key == null)
      {
        $key = $day->get_day_type_id();
        continue;
      }
      $key .= ',' . $day->get_day_type_id();
    }
    $key .= ' ';
    $key .= $this->get_timeranges_str();
    return $key;
  }
}
