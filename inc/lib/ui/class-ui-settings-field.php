<?php

/** 
 * UISettingsField
 * This Class is used to add a Setting to a Section 
 * and register it as an Option.
 * If we choose not to register it, set_register(false), 
 * then the ui will not use an option, so you have to
 * override this class and implement the get_value() by yourself
 * 
 * The UISettingsField shows a Textbox by default. By overriding
 * the method show_value(..) of this class, it is possible to 
 * show other control flavors (Checkbox, DropDown, TextArea)
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class UISettingsField
{
  private $_title;
  private $_id;
  private $_description;
  private $_register = true;
  private $_values = array();
  private $_defaultvalue = null;
  private $_disabled = false;

  public function __construct($id, $title)
  {
    $this->_id = $id;
    $this->_title = $title;
  }

  public function admin_init($menu_id, $group_id, $section_id)
  {
    if( $this->is_register() )
    {
      $args = array(
            'type' => 'string', 
            'sanitize_callback' => array($this, 'validate'));
      if(!empty($this->get_defaultvalue()))
      {
        $args['default'] = $this->get_defaultvalue(); 
      }
      register_setting( $group_id, 
                        $this->get_id(), 
                        $args);
    }
    add_settings_field( $this->get_id(), 
                        $this->get_title(),
                        array($this, 'show_value'),
                        $menu_id,
                        $section_id);
  }

  public function validate($input)
  {
    return $input;
  }

  function show_value()
  {
    $id = $this->get_id();
    $description = $this->get_description();
    $setting = esc_attr( $this->get_value() );
    $disabled_text = $this->get_disabled_text();
    echo "<input type='text' name='$id' value='$setting' $disabled_text/>";
    if(!empty($description))
    {
      echo "<br/><span><em>$description</em></span>";
    }
  }

  public function get_value()
  {
    $id = $this->get_id();
    return get_option( $id );
  }

  public function get_values()
  {
    return $this->_values;
  }

  public function add_value($key, $description)
  {
    array_push($this->_values, 
               new UISettingsFieldValue($key, $description));
  }

  /**
   * Register the field, so it will be
   * updated in the wp options table.
   * default is set on true.
   */
  public function set_register($register)
  {
    $this->_register = $register;
  }
  
  public function is_register()
  {
    return $this->_register;
  }

  public function set_description($description)
  {
    $this->_description = $description;
  }

  public function get_description()
  {
    return $this->_description;
  }

  public function set_defaultvalue($defaultvalue)
  {
    $this->_defaultvalue = $defaultvalue;
  }

  public function get_defaultvalue()
  {
    return $this->_defaultvalue;
  }

  public function set_disabled($disabled)
  {
    $this->_disabled = $disabled;
  }

  public function is_disabled()
  {
    return $this->_disabled;
  }

  public function get_disabled_text()
  {
    $disabled_text = '';
    if($this->is_disabled())
    {
      $disabled_text = " disabled='true'";
    }
    return $disabled_text;
  }

  public function get_id()
  {
    return $this->_id;
  }

  public function get_title()
  {
    return $this->_title;
  }

}

/** 
 * UISettingsFieldValue
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class UISettingsFieldValue
{
  private $_key;
  private $_description;

  public function __construct($key, $description)
  {
    $this->_key = $key;
    $this->_description = $description;
  }

  public function get_key()
  {
    return $this->_key;
  }

  public function get_description()
  {
    return $this->_description;
  }
}

/** 
 * UISettingsDropDownField
 * implementation of the UISettingsField with a DropDown box
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class UISettingsDropDownField extends UISettingsField
{
  function show_value()
  {
    $id = $this->get_id();
    $setting = esc_attr( $this->get_value() );
    $dropdown_values = $this->get_values();

    echo "<select id='$id' name='$id'>";
    foreach($dropdown_values as $v) 
    {
      $key = $v->get_key();
      $desc = $v->get_description();
		  $selected = ($setting == $key) ? 'selected="selected"' : '';
		  echo "<option value='$key' $selected>$desc</option>";
    }
	  echo "</select>";
  }
}

/** 
 * UISettingsCheckBoxField
 * implementation of the UISettingsField with a Checkbox
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class UISettingsCheckBoxField extends UISettingsField
{
  function show_value() 
  {
    $id = $this->get_id();
    $setting = $this->get_value();
    $description = $this->get_description();
    $disabled_text = $this->get_disabled_text();
    $checked = '';
	  if($setting) 
    { 
      $checked = ' checked="checked" '; 
    }
	  echo "<input ".$checked." id='$id' name='$id' type='checkbox' $disabled_text/>";
    if(!empty($description))
    {
      echo "<span><em>$description</em></span>";
    }
  }
}

/** 
 * UISettingsTextAreaField
 * implementation of the UISettingsField with a TextArea box
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class UISettingsTextAreaField extends UISettingsField
{
  function show_value()
  {
    $id = $this->get_id();
    $disabled_text = $this->get_disabled_text();
    $setting = esc_attr( $this->get_value() );
	  echo "<textarea id='$id' name='$id' rows='20' cols='50' type='textarea' $disabled_text>$setting</textarea>";
}
  }

