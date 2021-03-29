<?php

/** 
 * WPAbstractModule
 * This Class is used to load a Plugin on the right moment
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
abstract class WPAbstractModule
{
  private $_parent_module = null;
  private $_modules = array();
  private $_loader;
  private $_dir;
  private $_name;
  private $_description;

  public function __construct($name)
  {
    $this->_name = $name;
    $mc = WPModuleConfiguration::get_instance();
    $mc->register_module($this);
    $this->_loader = new WPModuleLoader();
  }

  public function get_id()
  {
    $dir = $this->get_dir();
    $index = strrpos($dir, '/', -2);
    $id = substr($dir, $index + 1);
    return $id;
  }

  public function get_module_enabled_id()
  {
    return $this->get_id() . '-enabled';
  }

  public function get_name()
  {
    return $this->_name;
  }

  public function set_description($description)
  {
    return $this->_description = $description;
  }

  public function get_description()
  {
    return $this->_description;
  }

  public function set_parent_module($module)
  {
    $this->_parent_module = $module;
  }

  public function get_parent_module()
  {
    return $this->_parent_module;
  }

  public function is_root_module()
  {
    return empty($this->get_parent_module());
  }

  public function add_module($module)
  {
    array_push($this->_modules, $module);
    $module->set_parent_module($this);
    return $module;
  }

  public function get_modules()
  {
    return $this->_modules;
  }

  /**
   * Return false if there are child modules
   */
  public function has_module_enabled_option()
  {
    if($this->is_root_module())
    {
      return false;
    }

    if(!empty($this->get_modules_enabled()))
    {
      return false;
    }

    $parent = $this->get_parent_module();
    if(!$parent->is_module_enabled())
    {
      return false;
    }
    return true;
  }

  /** 
   * If the Module is a "Leaf" Module then it can be
   * enabled and disabled by the User.
   * Otherwise the Module is enabled if one of its 
   * Child Modules is enabled.
   * If the Module is the root module, then it is always enabled
   * because it is automatically enabled wenn the Plugin is enabled
   */
  public function is_module_enabled()
  {
    if($this->is_root_module())
    {
      return true;
    }

    return get_option($this->get_module_enabled_id(), false);
  }

  public function get_modules_enabled()
  {
    $enabled_modules = array();
    foreach($this->get_modules() as $module)
    {
      if($module->is_module_enabled())
      {
        array_push($enabled_modules, $module);
      }
    }
    return $enabled_modules;
  }

  public function get_loader()
  {
    return $this->_loader;
  }

  public function is_base_module()
  {
    return false;
  }

  final public function do_setup_includes()
  {
    $this->setup_includes($this->get_loader());
    foreach($this->get_modules_enabled() as $module)
    {
      $module->do_setup_includes();
    }
  }

  final public function do_setup()
  {
    $loader = $this->get_loader();
    $loader->add_action('update_option_' . $this->get_module_enabled_id() , 
                        $this, 
                        'do_module_activation_deactivation', 
                        10, 
                        2);

    $this->setup($loader);
    foreach($this->get_modules_enabled() as $module)
    {
      $module->do_setup();
    }
  }

  function do_module_activation_deactivation($old_value, $value)
  {
    // If a module is enabled, we habe to call module_activation
    if(!$old_value && $value)
    {
      $this->do_module_activate();
    }

    // If a module is disabled, we habe to call module_deactivation
    if($old_value && !$value)
    {
      $this->do_module_deactivate();
    }
  }

  final public function do_load_includes()
  {
    $module_dir = $this->get_dir();
    $loader = $this->get_loader();
    $loader->load_includes($module_dir);
    foreach($this->get_modules_enabled() as $module)
    {
      $module->do_load_includes();
    }
  }

  final public function do_register_filters()
  {
    $loader = $this->get_loader();
    $loader->register_filters();
    foreach($this->get_modules_enabled() as $module)
    {
      $module->do_register_filters();
    }
  }

  final public function do_register_actions()
  {
    $loader = $this->get_loader();
    $loader->register_actions();
    foreach($this->get_modules_enabled() as $module)
    {
      $module->do_register_actions();
    }
  }

  final public function do_execute_starters()
  {
    $loader = $this->get_loader();
    $loader->execute_starters();
    foreach($this->get_modules_enabled() as $module)
    {
      $module->do_execute_starters();
    }
  }

  final public function do_module_activate()
  {
    $this->module_activate();
  }

  final public function do_module_deactivate()
  {
    foreach($this->get_modules_enabled() as $module)
    {
      $module->do_module_deactivate();
    }
    $this->module_deactivate();
  }

  final public function do_module_uninstall()
  {
    $this->module_uninstall();
    foreach($this->get_modules_enabled() as $module)
    {
      $module->do_module_uninstall();
    }
  }

  /**
   * Used to add includes over $loader->add_include( .. )  
   */
  public abstract function setup_includes($loader );

  /**
   * Used to register filters, actions and starters
   */
  public abstract function setup($loader );
  
  public abstract function module_activate();

  public abstract function module_deactivate();

  public abstract function module_uninstall();

  private function get_dir() 
  {
    if(empty($this->_dir))
    {
      $rc = new ReflectionClass(get_class($this));
      $dir = dirname($rc->getFileName());
      $this->_dir = $dir;
    }
    return $this->_dir;
  }
}

