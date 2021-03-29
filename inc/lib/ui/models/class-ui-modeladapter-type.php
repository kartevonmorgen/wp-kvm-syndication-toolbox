<?php

class UIModelAdapterType
{
  public const TEXT = 'TEXT';
  public const TEXTAREA = 'TEXTAREA';
  public const BOOLTYPE = 'BOOLTYPE';
  public const COMBOBOX = 'COMBOBOX';

  private $_id;
  private $_viewadapter_type;

  public function __construct($id, $viewadapter_type)
  {
    $this->_id = $id;
    $this->_viewadapter_type = $viewadapter_type;
  }

  public function get_id()
  {
    return $this->_id;
  }

  public function get_viewadapter_type()
  {
    return $this->_viewadapter_type;
  }
}
