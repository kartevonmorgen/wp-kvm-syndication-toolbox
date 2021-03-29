<?php

/** 
 * UISettingsSection
 * This Class is used to add a Settings Section to an Settings Page
 * it uses the Settings API 
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class UISettingsSection
{
  private $_title;
  private $_id;
  private $_descriptions = array();

  private $_fields = array();

  public function __construct($id, $title)
  {
    $this->_id = $id;
    $this->_title = $title;
  }

  public function add_field($field)
  {
    array_push($this->_fields, $field);
    return $field;
  }

  public function add_textfield($field_id, $field_title)
  {
    return $this->add_field( new UISettingsField($field_id, 
                                                 $field_title) );
  }

  public function add_textarea($field_id, $field_title)
  {
    return $this->add_field( new UISettingsTextAreaField($field_id, 
                                                         $field_title) );
  }

  public function add_dropdownfield($field_id, $field_title)
  {
    return $this->add_field( new UISettingsDropDownField($field_id, 
                                                         $field_title));
  }

  public function add_checkbox($field_id, $field_title)
  {
    return $this->add_field( new UISettingsCheckBoxField($field_id, 
                                                         $field_title));
  }

  public function admin_init($menu_id, $group_id)
  {
    add_settings_section( $this->get_id(),
                          $this->get_title(),
                          array($this,'the_description'),
                          $menu_id );

    foreach($this->get_fields() as $field)
    {
      $field->admin_init($menu_id, $group_id, $this->get_id());
    }
  }

  public function set_description($description)
  {
    $this->_descriptions = array();
    $this->add_description($description);
  }

  public function add_description($description)
  {
    array_push($this->_descriptions, $description);
  }

  private function get_descriptions()
  {
    return $this->_descriptions;
  }

  function the_description()
  {
    foreach($this->get_descriptions() as $description)
    {
      echo '<p>' . $description . '</p>';
    }
  }

  public function get_fields()
  {
    return $this->_fields;
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
