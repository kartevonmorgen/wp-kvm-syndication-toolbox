<?php

/**
 * OpeningHours for saving and loading the openinghours structure
 * in one Field: $_key 
 * Follwing https://wiki.openstreetmap.org/wiki/Key:opening_hours
 * but not fully implemented
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class OpeningHours  
{
  private $_day_types;
  private $_key;
  private $_days;

  public function __construct()
  {
    $this->_day_types = new OpeningHoursDayTypes();

    $days = array();
    foreach($this->_day_types->get_day_types() as $day_type)
    {
      $day = new OpeningHoursDay($day_type);
      $days[$day_type->get_id()] = $day;
    }
    $this->_days = $days;
  }

  public function get_day_types()
  {
    return $this->_day_types;
  }

  public function get_key()
  {
    return $this->_key;
  }

  public function set_key($key)
  {
    $this->_key = $key;
    $this->calculate_days();
  }

  public function get_days()
  {
    return $this->_days;
  }

  public function has_openinghours()
  {
    foreach($this->get_days() as $day)
    {
      if($day->has_timeranges())
      {
        return true;
      }
    }
    return false;
  }

  private function calculate_days()
  {
    $types = $this->get_day_types();
    $days = $this->get_days();
    $key = $this->get_key();

    if(empty($key))
    {
      return $days;
    }
    $key_range_parts = explode (";", $key);
    foreach($key_range_parts as $key_range_part)
    {
      $key_range = explode (" ", trim($key_range_part));
    
      if(empty($key_range))
      {
        continue;
      }

      $key_days_part = $key_range[0];
      $day_types_for_range = 
        $types->get_day_types_for_range($key_days_part);

      $key_times_part_range = $key_range[1];
      $key_times_parts = explode (",", $key_times_part_range);
      
      $timeranges = array();
      $index = 0;
      foreach($key_times_parts as $key_times_part)
      {
        array_push($timeranges,
          new OpeningHoursTimeRange($key_times_part, $index));
        $index = $index + 1;
      }

      // This can override the timerange of a day
      // if multiple days with different times are mentioned.
      foreach($day_types_for_range as $day_type_for_range)
      {
        $day = $days[$day_type_for_range->get_id()];
        foreach($timeranges as $timerange)
        {
          $day->add_timerange($timerange);
        }
      }
    }

    $this->_days = $days;
    return $days;
  }

  public function calculate_key()
  {
    $key = null;
    $sets = $this->create_sets_by_timeranges();
    foreach($sets as $set)
    {
      if($key == null)
      {
        $key = $set->get_unique_key();
        continue;
      }
      $key .= '; ';
      $key .= $set->get_unique_key();
    }
    $this->_key = $key;
    return $key;
  }

  private function create_sets_by_timeranges()
  {
    $sets = array();
    $days = $this->get_days();
    do
    {
      $day = array_shift($days);
      if($day == null)
      {
        continue;
      }
      
      if(!$day->has_timeranges())
      {
        continue;
      }
      
      $set = new OpeningHoursTimeRangeSet($day->get_timeranges());
      $set->add_day($day);

      $newdays = array();
      foreach($days as $nextday)
      {
        if($set->has_same_timeranges($nextday))
        {
          $set->add_day($nextday);
          continue;
        }
        array_push($newdays, $nextday);
      }
      $days = $newdays;
      $set->reorder_days();
      array_push($sets,  $set);
    }
    while(count($days) > 0);
    return $sets;
  }

  /**
   * Extract the OpeningDays from a submitted Post
   * to determinate the key
   * @return the key
   */
  public function extract_key_from_post($id_prefix)
  {
    foreach($this->get_days() as $day)
    {
      $timeranges = array();
      for($index=0; $index<2; $index++)
      {
        $vid = $day->get_view_id_start_time($id_prefix, $index);
        if(array_key_exists ( $vid , $_POST ))
        {
          $start_value = $_POST[$vid];
        }

        $vid = $day->get_view_id_end_time($id_prefix, $index);
        if(array_key_exists ( $vid , $_POST ))
        {
          $end_value = $_POST[$vid];
        }
        $timerange = new OpeningHoursTimeRange($start_value . '-' . $end_value, 
                                                 $index );
        $day->add_timerange($timerange);
      }
    }
    return $this->calculate_key();
  }
}
