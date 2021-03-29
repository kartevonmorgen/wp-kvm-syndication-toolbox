<?php

class SSImportType
{
  private $_id;
  private $_name;
  private $_class;

	function __construct($id, $name, $class)
  {
    $this->_id = $id;
    $this->_name = $name;
    $this->_class = $class;
  }

  public function get_id()
  {
    return $this->_id;
  }

  public function get_name()
  {
    return $this->_name;
  }

  public function get_clazz()
  {
    return $this->_class;
  }
}
