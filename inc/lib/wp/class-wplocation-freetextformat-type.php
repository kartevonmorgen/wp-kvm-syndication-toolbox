<?php

class WPLocationFreeTextFormatType
{
  private $_id;
  private $_name;
  private $_default;

	function __construct($id, $name, $default = false)
  {
    $this->_id = $id;
    $this->_name = $name;
    $this->_default = $default;
  }

  public function get_id()
  {
    return $this->_id;
  }

  public function get_name()
  {
    return $this->_name;
  }

  public function is_default()
  {
    return $this->_default;
  }
}
