<?php

/**
 * WPAbstractModuleProvider can be used to access
 * the current module
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
abstract class WPAbstractModuleProvider
{
  private $_current_module;

  public function __construct($current_module) 
  {
    $this->_current_module = $current_module;
  }

  public function get_current_module()
  {
    return $this->_current_module;
  }

  public function get_parent_module()
  {
    return $this->get_current_module()->get_parent_module();
  }

  public function get_root_module()
  {
    $mc = WPModuleConfiguration::get_instance();
    return $mc->get_root_module();
  }

  public function is_module_enabled($module_id)
  {
    $mc = WPModuleConfiguration::get_instance();
    return $mc->is_module_enabled($module_id); 
  }

  public function get_module($module_id)
  {
    $mc = WPModuleConfiguration::get_instance();
    return $mc->get_module($module_id); 
  }

}

