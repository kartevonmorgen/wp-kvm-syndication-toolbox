<?php

/**
 * OpeningHours for saving and  
 * Cronjobs
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class OpeningHoursDayTypes  
{
  private $_first;
  private $_day_types;

  public function __construct()
  {
    $this->_day_types = array();
      
    $previous = $this->add_type('Mo', 'Montag', null);
    $this->_first = $previous; 

    $previous = $this->add_type('Tu', 'Dienstag', $previous);
    $previous = $this->add_type('We', 'Mittwoch', $previous);
    $previous = $this->add_type('Th', 'Donnerstag', $previous);
    $previous = $this->add_type('Fr', 'Freitag', $previous);
    $previous = $this->add_type('Sa', 'Samstag', $previous);
    $previous = $this->add_type('Su', 'Sonntag', $previous);

    $this->_first->set_previous($previous);
    $previous->set_next($this->_first);
  }

  private function get_first()
  {
    return $this->_first;
  }

  public function get_day_types()
  {
    return $this->_day_types;
  }


  private function add_type($id, $title, $previous)
  {
    $type = new OpeningHoursDayType($id, $title);
    $type->set_previous($previous);
    if(!empty($previous))
    {
      $previous->set_next($type);
    }
    array_push($this->_day_types, $type);
    return $type;
  }

  public function get_day_types_for_range($range)
  {
    $types = array();
    $type_range = explode ("-", $range);
    if (count ($type_range) == 1)
    {
      // Something like No-Fr not found, so it is not a sequence
      $type_range = explode (",", $range);
      if (count ($type_range) > 0)
      {
        foreach($type_range as $type_id)
        {
          array_push($types, 
                     $this->get_type_by_id($type_id));
        }
      }
      return $types;
    }

    // We have a sequence, something like Mo-Th
    $type_id = $type_range[0];
    $type = $this->get_type_by_id($type_id);
    array_push($types, $type);
      
    $type_id_end = $type_range[1];
    do
    {
      $type = $type->get_next();
      array_push($types, $type);
    }
    while($type_id_end !== $type->get_id());
    return $types;
  }

  public function get_type_by_id($id)
  {
    $first = $this->get_first();
    $current = $first;

    do
    {
      if($current->get_id() == $id)
      {
        return $current;
      }
      $current = $current->get_next();
    }
    while($first->get_id() !== $current->get_id());
    return null;
  }
}
