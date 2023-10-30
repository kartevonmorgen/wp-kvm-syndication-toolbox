<?php

/** 
 * WPAbstractPlugin
 * This Class is used to load a Plugin on the right moment
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
abstract class WPAbstractPlugin extends WPAbstractModule
{
  private $_plugin;

  public function register( $plugin, $priority = 10)
  {
    $this->_plugin = $plugin;

    add_action( 'init', 
                array($this, 'do_init'), 
                $priority );
    register_activation_hook($plugin, 
                            array($this, 'do_activate'));
    register_deactivation_hook( $plugin, 
                            array($this, 'do_deactivate'));
    register_uninstall_hook( $plugin, 'plugin_do_uninstall');
  }

  public function get_plugin_dir()
  {
    return plugin_dir_path($this->get_plugin());
  }

  public function get_plugin_id()
  {
    return plugin_basename($this->get_plugin());
  }

  public function get_plugin()
  {
    return $this->_plugin;
  }

  public function get_plugin_name()
  {
    $id = $this->get_plugin_id();
    $pos = strpos($id, '/');
    return substr($id, 0 , $pos);
  }

  public function do_init()
  {
    $this->setup_modules();

    $this->do_setup_includes();
    $this->do_load_includes();

    $this->do_setup();

    $this->do_register_filters();
    $this->do_register_actions();
    $this->do_execute_starters();
  }

  /**
   * Used to setup modules, which are loaded after the 
   * Base Plugin Module has been loaded 
   * (AbstractPlugin is also a Module)
   */
  public abstract function setup_modules();

  public function do_activate()
  {
    $this->setup_modules();

    $this->do_setup_includes();
    $this->do_load_includes();

    $this->do_setup();

    $this->do_register_filters();
    $this->do_register_actions();
    $this->do_execute_starters();

    $this->do_module_activate();
  }

  public function is_activated()
  {
    return is_plugin_active($this->get_plugin_id());
  }

  public function do_deactivate()
  {
    $this->deactivate();
    $this->do_module_deactivate();
  }

  public function deactivate()
  {
  }

  public function do_uninstall()
  {
    $this->uninstall();
    $this->do_module_uninstall();
  }

  public function uninstall()
  {
  }

}

function plugin_do_uninstall()
{
  $mc = WPModuleConfiguration::get_instance();
  $root = $mc->get_root_module();
  if(!empty($root))
  {
    $root->do_uninstall();
  }
}


