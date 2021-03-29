<?php
/** 
 * This WPModuleConfiguration can be used to get access to a Module 
 * 
 * The WPModuleConfiguration is an Singelton, 
 * so there can be only one instance
 * in a Wordpress Session.
 * It can be used for example in this way.
 * 
 * $mc = WPModuleConfiguration::get_instance();
 * $mc->get_module($id_of_module);
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class WPModuleConfiguration 
{
  private static $instance = null;

  private $_modules = array();

  private function __construct() 
  {
  }

  /** 
   * The object is created from within the class itself
   * only if the class has no instance.
   */
  public static function get_instance()
  {
    if (self::$instance == null)
    {
      self::$instance = new WPModuleConfiguration();
    }
    return self::$instance;
  }

  public function get_modules()
  {
    return $this->_modules;
  }

  public function register_module($module)
  {
    $this->_modules[$module->get_id()] = $module;
  }

  public function get_module($id)
  {
    return $this->get_modules()[$id];
  }

  public function is_module_enabled($id)
  {
    $module = $this->get_module($id);
    if(empty($module))
    {
      return false;
    }
    return $module->is_module_enabled();
  }

  public function get_root_module()
  {
    foreach($this->get_modules() as $module)
    {
      if($module->is_root_module())
      {
        return $module;
      }
    }
    return null;
  }


}
