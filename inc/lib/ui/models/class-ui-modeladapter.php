<?php

abstract class UIModelAdapter
{
  private $_value;
  private $_defaultvalue;
  private $_loadedvalue;
  private $_value_setted;
  private $_value_changed;
  private $_id;
  private $_title;
  private $_type_id;
  private $_description;
  private $_choices = array();
  private $_disabled = false;
  private $_validate = false;
  private $_backgroundcolor = null;

  private $_model;

  public function __construct($id, $type_id, $title = '')
  {
    $this->_id = $id;
    $this->_type_id = $type_id;
    $this->set_title($title);
    $this->set_default_value('');
  }

  public function get_id()
  {
    return $this->_id;
  }

  public function get_type_id()
  {
    return $this->_type_id;
  }

  public function get_type()
  {
    if(empty($this->get_model()))
    {
      return null;
    }
    return $this->get_model()->get_ma_type($this->get_type_id());
  }

  public function get_viewadapter_type()
  {
    $type = $this->get_type();
    if(empty($type))
    {
      return null;
    }
    return $type->get_viewadapter_type();
  }

  public function set_model($model)
  {
    $this->_model = $model;
  }

  public function get_model()
  {
    return $this->_model;
  }

  public function get_property($key)
  {
    return $this->get_model()->get_property($key);
  }

  public function set_title($title)
  {
    $this->_title = $title;
  }

  public function get_title()
  {
    return $this->_title;
  }

  public function set_description($description)
  {
    $this->_description = $description;
  }

  public function get_description()
  {
    return $this->_description;
  }

  public function add_choice($id, $name = null)
  {
    array_push( $this->_choices, new UIChoice($id, $name));
  }

  public function get_choices()
  {
    return $this->_choices;
  }

  public function set_disabled($disabled)
  {
    $this->_disabled = $disabled;
  }

  public function is_disabled()
  {
    return $this->_disabled;
  }

  public function set_validate($validate)
  {
    $this->_validate = $validate;
  }

  public function is_validate()
  {
    return $this->_validate;
  }

  public function set_backgroundcolor_id($backgroundcolorid)
  {
    $uicolor = $this->get_model()->get_bgcolor($backgroundcolorid);
    if(empty($uicolor))
    {
      return;
    }
    $this->_backgroundcolor = $uicolor;
  }

  public function get_backgroundcolor_code()
  {
    if(empty($this->_backgroundcolor))
    {
      return null;
    }
    return $this->_backgroundcolor->get_code();
  }

  public function set_value_setted($value_setted)
  {
    $this->_value_setted = $value_setted;
  }

  public function is_value_setted()
  {
    return $this->_value_setted;
  }

  public function set_value_changed($value_changed)
  {
    $this->_value_changed = $value_changed;
  }

  public function is_value_changed()
  {
    return $this->_value_changed;
  }

  public function get_value()
  {
    if($this->is_value_setted())
    {
      return $this->_value;
    }
    return $this->get_loaded_value();
  }

  public function set_value($value)
  {
    $this->_value = $value;
    $this->set_value_setted(true);
    if($value != $this->get_loaded_value())
    {
      $this->set_value_changed(true);
    }
  }

  public function get_default_value()
  {
    return $this->_defaultvalue;
  }

  public function set_default_value($defaultvalue)
  {
    $this->_defaultvalue = $defaultvalue;
  }

  public function get_loaded_value()
  {
    return $this->_loadedvalue;
  }

  public function set_loaded_value($value)
  {
    $this->_loadedvalue = $value;
  }

  public abstract function load_value();

  public function validate_value($errors)
  {
    if(!$this->is_validate())
    {
      return $errors;
    }

    $value = $this->get_value();
    if ( empty( $value )
       || ! empty( $value )
       && trim( $value ) == '' ) 
    {
      $errors->add(
         $this->get_id().'_error',
           'Fehler: ' . $this->get_title() . ' fehlt!!');
    }
    return $errors;
  }

  public abstract function save_value();
}
