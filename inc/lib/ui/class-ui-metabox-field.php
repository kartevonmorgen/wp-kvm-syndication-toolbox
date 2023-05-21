<?php

/** 
 * UIMetaboxField
 * This Class is used to add a Field to a Metabox
 * and register it as an Option.
 * If we choose not to register it, set_register(false), 
 * then the ui will not use an option, so you have to
 * override this class and implement the get_value() by yourself
 * 
 * The UIMetaboxField shows a Textbox by default. By overriding
 * the method show_value(..) of this class, it is possible to 
 * show other control flavors (Checkbox, DropDown, TextArea)
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class UIMetaboxField
{
  private $_title;
  private $_id;
  private $_description;
  private $_register = true;
  private $_values = array();
  private $_defaultvalue = null;
  private $_disabled = false;
  private $_escape_html = true;

  public function __construct($id, $title)
  {
    $this->_id = $id;
    $this->_title = $title;
  }

  public function validate($input)
  {
    return $input;
  }

  function show_value($post)
  {
    $id = $this->get_id();
    $title = $this->get_title();
    $description = $this->get_description();
    $disabled_text = $this->get_disabled_text();
    $value = $this->get_value($post);
    if($this->is_escape_html())
    {
      $value = esc_attr( $value );
    }
    echo "<p>$title</p>";
    echo "<input style='width:100%' type='text' name='$id' value='$value' $disabled_text/>";
    if(!empty($description))
    {
      echo "<br/><span style='font-size:0,8 em'><em>$description</em></span>";
    }
  }

  public function get_value($post)
  {
    $id = $this->get_id();

    if ( metadata_exists( 'post', $post->ID, $id ) ) 
    {
      $value = get_post_meta( $post->ID, $id, true );
      if($this->is_checkbox() && $value == 'off')
      {
        return false;
      }
      return $value;
    }
    return $this->get_defaultvalue($post);
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

  public function is_checkbox()
  {
    return false;
  }

  public function save_value($post_id, $value = null)
  {
    if(!$this->is_register())
    {
      return;
    }

    if(empty($value))
    {
      if(array_key_exists ( $this->get_id() , $_POST ))
      {
        $value = $_POST[$this->get_id()];
      }
      else if($this->is_checkbox())
      {
        $value = 'off';
      }
    }

    if ( isset( $value ) )
    {
      if(!$this->is_checkbox() && $this->is_escape_html())
      {
        $value = sanitize_text_field( $value );
      }
      if($this->is_checkbox() && $value == false)
      {
        $value = 'off';
      }
      update_post_meta( $post_id, 
                        $this->get_id(), 
                        $value );
    }
  }


  public function set_description($description)
  {
    $this->_description = $description;
  }

  public function get_description()
  {
    return $this->_description;
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

  public function set_defaultvalue($defaultvalue)
  {
    $this->_defaultvalue = $defaultvalue;
  }

  public function get_defaultvalue($post)
  {
    return $this->_defaultvalue;
  }

  public function get_id()
  {
    return $this->_id;
  }

  public function get_title()
  {
    return $this->_title;
  }

  public function set_escape_html($escape_html)
  {
    $this->_escape_html = $escape_html;
  }

  public function is_escape_html()
  {
    return $this->_escape_html;
  }

}

/** 
 * UIMetaboxFieldValue
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class UIMetaboxFieldValue
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
 * UIMetaboxDropDownField
 * implementation of the UIMetaboxField with a DropDown box
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class UIMetaboxDropDownField extends UIMetaboxField
{
  function show_value($post)
  {
    $id = $this->get_id();
    $title = $this->get_title();
    $value = esc_attr( $this->get_value($post) );
    $dropdown_values = $this->get_values();

    echo "<p>$title</p>";
    echo "<select id='$id' name='$id'>";
    foreach($dropdown_values as $v) 
    {
      $key = $v->get_key();
      $desc = $v->get_description();
		  $selected = ($value == $key) ? 'selected="selected"' : '';
		  echo "<option value='$key' $selected>$desc</option>";
    }
	  echo "</select>";
  }
}

/** 
 * UIMetaboxCheckBoxField
 * implementation of the UIMetaboxField with a Checkbox
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class UIMetaboxCheckBoxField extends UIMetaboxField
{
  function show_value($post) 
  {
    $id = $this->get_id();
    $title = $this->get_title();
    $value = $this->get_value($post);
    $description = $this->get_description();
    $checked = '';
	  if($value) 
    { 
      $checked = ' checked="checked" '; 
    }
    echo "<p>$title</p>";
	  echo "<input ".$checked." id='$id' name='$id' type='checkbox' />";
    if(!empty($description))
    {
      echo "<span><em>$description</em></span>";
    }
  }

  public function is_checkbox()
  {
    return true;
  }

}

/** 
 * UIMetaboxTextAreaField
 * implementation of the UIMetaboxField with a TextArea box
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class UIMetaboxTextAreaField extends UIMetaboxField
{
  function show_value($post)
  {
    $id = $this->get_id();
    $title = $this->get_title();
    $disabled_text = $this->get_disabled_text();
    $value = esc_attr( $this->get_value($post) );
    echo "<p>$title</p>";
	  echo "<textarea id='$id' name='$id' rows='4' cols='50' type='textarea' $disabled_text>$value</textarea>";
  }
}

/** 
 * UIMetaboxDateField
 * implementation of the UIMetaboxField with a Datepicker box
 * Stored as Unixtimestamp
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class UIMetaboxDateField extends UIMetaboxField
{
  function show_value($post)
  {
    $id = $this->get_id();
    $title = $this->get_title();
    $description = $this->get_description();
    $disabled_text = $this->get_disabled_text();
    $value = esc_attr( $this->get_value($post) );
    echo "<p>$title</p>";
    echo "<input style='width:300px' type='datetime-local' name='$id' value='$value' $disabled_text/>";
    if(!empty($description))
    {
      echo "<br/><span style='font-size:0,8 em'><em>$description</em></span>";
    }
  }
    
  public function get_value($post)
  {
    $unixtime = parent::get_value($post);
    if(empty($unixtime) || !is_numeric($unixtime))
    {
      return null;
    }
    $dt = new DateTime(
      date( 'Y-m-d H:i:s', $unixtime ),
      new DateTimeZone('UTC'));
    $localtimezone = wp_timezone();
    $dt->setTimezone( $localtimezone );
    return $dt->format( 'Y-m-d\\TH:i' );
  }
    
  public function save_value($post_id, $value = null)
  {
    if(empty($value))
    {
      if(array_key_exists ( $this->get_id() , $_POST ))
      {
        $value = $_POST[$this->get_id()];
      }
    }

    if(empty($value))
    {
      parent::save_value($post_id, $value);
      return;
    }
    $timezone = wp_timezone();
    $dt = new DateTime($value, $timezone );
    parent::save_value($post_id, $dt->getTimestamp());
  }
}
