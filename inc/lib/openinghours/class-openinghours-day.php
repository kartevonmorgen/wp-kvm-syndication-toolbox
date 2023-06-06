<?php

/**
 * OpeningHoursDays  
 * according to https://wiki.openstreetmap.org/wiki/Key:opening_hours
 * not fully implemented
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class OpeningHoursDay  
{
  private $_day_type;
  private $_timeranges = array();

  public function __construct($day_type)
  {
    $this->_day_type = $day_type;

    $this->add_timerange( new OpeningHoursTimeRange('00:00-00:00',0));
    $this->add_timerange( new OpeningHoursTimeRange('00:00-00:00',1));
  }

  public function get_day_type()
  {
    return $this->_day_type;
  }

  public function get_day_type_id()
  {
    return $this->get_day_type()->get_id();
  }

  public function add_timerange($timerange)
  {
    $this->_timeranges[$timerange->get_index()] = $timerange;
  }

  public function has_timeranges()
  {
    if( empty($this->get_real_timeranges()))
    {
      return false;
    }

    if(count($this->get_real_timeranges()) == 0)
    {
      return false;
    }

    return true;
  }

  public function is_closed()
  {
    return !$this->has_timeranges();
  }

  public function get_timeranges()
  {
    return $this->get_real_timeranges();
  }

  public function get_all_timeranges()
  {
    return $this->_timeranges;
  }

  private function get_real_timeranges()
  {
    $last_timerange = null;

    $real_timeranges = array();
    foreach($this->_timeranges as $timerange)
    {
      if(!$timerange->is_default())
      {
        if($timerange->after($last_timerange))
        {
          array_push($real_timeranges, $timerange);
        }
        else
        {
          array_splice($real_timeranges, 0,0, array($timerange));
        }
        $last_timerange = $timerange;
      }
    }
    return $real_timeranges;
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

  public function get_view_id_start_time($id_prefix, $index)
  {
    $day_id = $id_prefix . '_' . $this->get_day_type_id(); 
    return $day_id . '_' . $index . '_start';
  }

  public function get_view_id_end_time($id_prefix, $index)
  {
    $day_id = $id_prefix . '_' . $this->get_day_type_id(); 
    return $day_id . '_' . $index . '_end';
  }
}
