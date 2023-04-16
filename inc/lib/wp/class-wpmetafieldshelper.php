<?php

class WPMetaFieldsHelper
{
  private $_prefix = '';
  private $_fields = array();
  private $_cvalues = array();
  private $_post_id;
  private $_force = false;

  public function __construct($post_id)
  {
    $this->_post_id = $post_id;
  }

  public function set_prefix($prefix)
  {
    $this->_prefix = $prefix;
  }

  public function get_prefix()
  {
    return $this->_prefix;
  }

  public function set_force_db($force)
  {
    $this->_force = $force;
  }

  public function is_force_db()
  {
    return $this->_force;
  }

  public function add_field($key)
  {
    array_push($this->_fields, $this->get_prefix() . $key);
  }

  public function get_fields()
  {
    return $this->_fields;
  }

  public function get_post_id()
  {
    return $this->_post_id;
  }

  public function get_value($k)
  {
    $key = $this->get_prefix() . $k;
    if($this->has_cache_value($key))
    {
      return $this->get_cache_value($key);
    }
    if($this->is_force_db())
    {
      $value = $this->get_in_db_value($key);
    }
    else if($this->is_in_memory($key))
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

  private function is_in_memory($key)
  {
    if(!array_key_exists($key, $_POST))
    {
      return false;
    }
    return isset($_POST[$key]);
  }

  private function get_in_memory_value($key)
  {
    if(!array_key_exists($key, $_POST))
    {
      return null;
    }
    return $_POST[$key];
  }

  private function get_in_db_value($key)
  {
    $post_id = $this->get_post_id();
    $value = get_post_meta( $post_id, $key, true );
    if($value == 'off')
    {
      return false;
    }
    return $value;
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
