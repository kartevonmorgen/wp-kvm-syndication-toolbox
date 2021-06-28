<?php

abstract class UIModel
{
  public const USER_ID = 'USER_ID';
  public const POST_ID = 'POST_ID';

  private $_modeladapters = array();
  private $_modeladaptertypes = array();
  private $_properties = array();
  private $_bgcolors = array();

  public function __construct()
  {
    $this->add_ma_type(
      new UIModelAdapterType(UIModelAdapterType::TEXT, 'UIVATextfield'));
    $this->add_ma_type(
      new UIModelAdapterType(UIModelAdapterType::TEXTAREA, 'UIVATextarea'));
    $this->add_ma_type(
      new UIModelAdapterType(UIModelAdapterType::BOOLTYPE, 'UIVACheckbox'));
    $this->add_ma_type(
      new UIModelAdapterType(UIModelAdapterType::COMBOBOX, 'UIVACombobox'));
  }

  public abstract function init();

  public function add_ma($modeladapter)
  {
    $modeladapter->set_model($this);
    array_push($this->_modeladapters, $modeladapter);
    return $modeladapter;
  }

  public function get_modeladapters()
  {
    return $this->_modeladapters;
  }

  public function get_modeladapter($id)
  {
    foreach($this->get_modeladapters() as $ma)
    {
      if( $ma->get_id() == $id )
      {
        return $ma;
      }
    }
    return null;
  }

  public function add_ma_type($ma_type)
  {
    array_push($this->_modeladaptertypes, $ma_type);
  }

  public function get_ma_types()
  {
    return $this->_modeladaptertypes;
  }

  public function get_ma_type($id)
  {
    foreach($this->get_ma_types() as $mat)
    {
      //echo '<p>MA TYPE ' . $id . ' = ' . $mat->get_id() . '</p>';
      if( $mat->get_id() == $id )
      {
        return $mat;
      }
    }
    return null;
  }

  public function get_viewadapter_type($id)
  {
    $ma = $this->get_modeladapter($id);
    if(empty($ma))
    {
      return null;
    }
    return $ma->get_viewadapter_type();
  }

  public function add_bgcolor($color)
  {
    array_push($this->_bgcolors, $color);
  }

  public function get_bgcolors()
  {
    return $this->_bgcolors;
  }

  public function get_bgcolor($id)
  {
    foreach($this->get_bgcolors() as $color)
    {
      if( $color->get_id() == $id )
      {
        return $color;
      }
    }
    return null;
  }

  public function load()
  {
    $this->load_model();

    foreach($this->get_modeladapters() as $modeladapter)
    {
      $modeladapter->load_value();
    }
  }

  protected function load_model()
  {
  }

  /**
   * Update Model with View values
   */
  protected function update()
  {
    foreach($this->get_modeladapters() as $ma)
    {
      if($ma->is_disabled())
      {
        continue;
      }

      if ( !array_key_exists($ma->get_id(), $_POST ) )
      {
        continue;
      }
      $ma->set_value( $_POST[$ma->get_id()] );
    }

    $this->update_model();
  }

  protected function update_model()
  {
  }

  /**
   * @param $errors WP_Error: 
   * ModelAdapter can add an error
   * on WP_Error
   */
  public function validate($viewadapter_ids, $errors)
  {
    $this->update();

    foreach($this->get_modeladapters() as $ma)
    {
      if(!in_array($ma->get_id(), $viewadapter_ids ))
      {
        continue;
      }

      if(!$ma->is_validate())
      {
        continue;
      }
      $errors = $ma->validate_value($errors);
    }
    return $this->validate_model($errors);
  }

  protected function validate_model($errors)
  {
    return $errors;
  }

  public function save()
  {
    $this->update();

    $this->before_save_model();
    foreach($this->get_modeladapters() as $ma)
    {
      if($ma->is_value_changed())
      {
        $ma->save_value();
      }
    }

    $this->save_model();

    foreach($this->get_modeladapters() as $ma)
    {
      $ma->set_loaded_value($ma->get_value());
      $ma->set_value_changed(false);
      $ma->set_value_setted(false);
    }
  }

  protected function before_save_model()
  {
  }

  protected function save_model()
  {
  }

  public function set_property($key, $value)
  {
    $this->_properties[$key] = $value;
  }

  public function get_property($key)
  {
    if(array_key_exists($key, $this->_properties))
    {
      return $this->_properties[$key];
    }
    return null;
  }
  
  public function set_value($id, $value)
  {
    $ma = $this->get_modeladapter($id);
    if(empty($ma))
    {
      return;
    }
    return $ma->set_value($value);
  }

  public function get_value($id)
  {
    $ma = $this->get_modeladapter($id);
    if(empty($ma))
    {
      return null;
    }
    return $ma->get_value();
  }

  public function get_title($id)
  {
    $ma = $this->get_modeladapter($id);
    if(empty($ma))
    {
      return null;
    }
    return $ma->get_title();
  }

  public function get_description($id)
  {
    $ma = $this->get_modeladapter($id);
    if(empty($ma))
    {
      return null;
    }
    return $ma->get_description();
  }

  public function get_choices($id)
  {
    $ma = $this->get_modeladapter($id);
    if(empty($ma))
    {
      return array();
    }
    return $ma->get_choices();
  }

  public function set_disabled($id, $value)
  {
    $ma = $this->get_modeladapter($id);
    if(empty($ma))
    {
      return;
    }
    return $ma->set_disabled($value);
  }

  public function is_disabled($id)
  {
    $ma = $this->get_modeladapter($id);
    if(empty($ma))
    {
      return false;
    }
    return $ma->is_disabled();
  }

  public function set_backgroundcolor_id($id, $backgroundcolorid)
  {
    $ma = $this->get_modeladapter($id);
    if(empty($ma))
    {
      return;
    }
    $ma->set_backgroundcolor_id($backgroundcolorid);
  }

  public function get_backgroundcolor_code($id)
  {
    $ma = $this->get_modeladapter($id);
    if(empty($ma))
    {
      return null;
    }
    return $ma->get_backgroundcolor_code();
  }

  public function is_value_changed($id)
  {
    $ma = $this->get_modeladapter($id);
    if(empty($ma))
    {
      return false;
    }
    return $ma->is_value_changed();
  }
}

