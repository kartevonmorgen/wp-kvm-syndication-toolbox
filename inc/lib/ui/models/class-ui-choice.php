<?php

class UIChoice
{
  private $_id;
  private $_name;

  public function __construct($id, $name = null)
  {
    $this->_id = $id;
    $this->_name = $name;
    if(empty($name))
    {
      $this->_name = $id;
    }
  }

  public function get_id()
  {
    return $this->_id;
  }

  public function get_name()
  {
    return $this->_name;
  }
}
