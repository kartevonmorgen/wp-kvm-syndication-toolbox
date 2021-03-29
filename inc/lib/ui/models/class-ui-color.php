<?php

class UIColor
{
  private $_id;
  private $_name;
  private $_code;

  public function __construct($id, $name, $code)
  {
    $this->_id = $id;
    $this->_name = $name;
    $this->_code = $code;
  }

  public function get_id()
  {
    return $this->_id;
  }

  public function get_name()
  {
    return $this->_name;
  }

  public function get_code()
  {
    return $this->_code;
  }
}
