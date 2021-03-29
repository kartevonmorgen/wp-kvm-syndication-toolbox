<?php

/** 
 * UIModuleSettingsCheckBoxField
 * Spezial Checkbox for enabling and disabling modules
 * includes:
 *   - only leaf Modules can be enabled (if the Module has no child-modules)
 *   - wenn a Module will be enabled, then we activate the Module
 *   - wenn a Module will be disabled, then we deactivate the Module
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class UIModuleSettingsCheckBoxField extends UISettingsCheckBoxField
{
  private $_module;

  public function __construct($module, $title)
  { 
    parent::__construct($module->get_module_enabled_id(), $title);
    $this->_module = $module;
    if(!$module->has_module_enabled_option())
    {
      $this->set_register(false);
      $this->set_disabled(true);
    }
  }

  private function has_enabled_option()
  {
    return $this->_enabled_option;
  }

  private function get_module()
  {
    return $this->_module;
  }

  public function get_value()
  {
    $module = $this->get_module();
    return $module->is_module_enabled();
  }

  public function validate($input)
  {
    return $input;
  }
}

