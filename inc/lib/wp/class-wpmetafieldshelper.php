<?php

class WPMetaFieldsHelper
{
  private $_fields = array();
  private $_cvalues = array();
  private $_post_id;

  public function __construct($post_id)
  {
    $this->_post_id = $post_id;
  }

  public function add_field($key)
  {
    array_push($this->_fields, $key);
  }

  public function get_fields()
  {
    return $this->_fields;
  }

  public function get_post_id()
  {
    return $this->_post_id;
  }

  public function get_value($key)
  {
    if($this->has_cache_value($key))
    {
      return $this->get_cache_value($key);
    }
    if($this->is_in_memory($key))
    {
      $value = $this->get_in_memory_value($key);
    }
    else
    {
      $value = $this->get_in_db_value($key);
    }
    $this->set_cache_value($key, $value);
    return $value;
  }

  private function has_cache_value($key)
  {
    return array_key_exists($key, $this->_cvalues);
  }

  private function get_cache_value($key)
  {
    return $this->_cvalues[$key];
  }

  private function set_cache_value($key, $value)
  {
    $this->_cvalues[$key] = $value;
  }

  public function is_in_memory($key)
  {
    return isset($_POST[$key]);
  }

  private function get_in_memory_value($key)
  {
    return $_POST[$key];
  }

  private function get_in_db_value($key)
  {
    $post_id = $this->get_post_id();
    return get_post_meta( $post_id, $key, true );
  }

  public function has_in_memory_values()
  {
    foreach($this->get_fields() as $field_key)
    {
      if($this->is_in_memory($field_key))
      {
        return true;
      }
    }
    return false;
  }

}
