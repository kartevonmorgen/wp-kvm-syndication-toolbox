<?php

/** 
 * WPLoader
 * This Class is used to load a Plugin or Module on the right moment
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class WPModuleLoader
{
  private $_includes = array();
  private $_starters = array();

  /**
	 * The array of actions registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array $_actions    
   *           The actions registered with WordPress to fire when the plugin loads.
	 */
	private $_actions = array();

	/**
	 * The array of filters registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array $_filters    
   *           The filters registered with WordPress to fire when the plugin loads.
	 */
	private $_filters = array();


  public function add_include($include)
  {
    array_push($this->_includes, $include);
  }

  public function get_includes()
  {
    return $this->_includes;
  }

  public function add_starter($starter)
  {
    array_push($this->_starters, $starter);
  }

  public function get_starters()
  {
    return $this->_starters;
  }

	/**
	 * Add a new action to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @param    string $hook 
   *           The name of the WordPress action that is being registered.
	 * @param    object $component        
   *           A reference to the instance of the object on which the action is defined.
	 * @param    string $callback 
   *           The name of the function definition on the $component.
	 * @param    int $priority 
   *           Optional. The priority at which the function should be fired. 
   *           Default is 10.
	 * @param    int $accepted_args    
   *           Optional. The number of arguments that should be passed 
   *           to the $callback. Default is 1.
	 */
	public function add_action( $hook, 
                              $component, 
                              $callback, 
                              $priority = 10, 
                              $accepted_args = 1 ) 
  {
		$this->_actions = $this->add( $this->_actions, 
                                  $hook, 
                                  $component, 
                                  $callback, 
                                  $priority, 
                                  $accepted_args );
	}

  public function get_actions()
  {
    return $this->_actions;
  }

	/**
	 * Add a new filter to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @param    string $hook
   *           The name of the WordPress filter that is being registered.
	 * @param    object $component
   *           A reference to the instance of the object on which the filter is defined.
	 * @param    string $callback
   *           The name of the function definition on the $component.
	 * @param    int $priority
   *           Optional. The priority at which the function should be fired. 
   *           Default is 10.
	 * @param    int $accepted_args    
   *           Optional. The number of arguments that should be 
   *           passed to the $callback. Default is 1
	 */
	public function add_filter( $hook, 
                              $component, 
                              $callback, 
                              $priority = 10, 
                              $accepted_args = 1 ) 
  {
		$this->_filters = $this->add( $this->_filters, 
                                  $hook, 
                                  $component, 
                                  $callback, 
                                  $priority, 
                                  $accepted_args );
	}

  public function get_filters()
  {
    return $this->_filters;
  }

	/**
	 * A utility function that is used to register 
   * the actions and hooks into a single collection.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    array $hooks The collection of hooks that is being registered 
   *                        (that is, actions or filters).
	 * @param    string $hook The name of the WordPress filter that is being registered.
	 * @param    object $component A reference to the instance of the object 
                                 on which the filter is defined.
	 * @param    string $callback  The name of the function definition 
                                 on the $component.
	 * @param    int $priority  The priority at which the function should be fired.
	 * @param    int $accepted_args The number of arguments that should be passed 
                                  to the $callback.
	 * @return   array The collection of actions and filters registered with WordPress.
	 */
	private function add( $hooks, 
                        $hook, 
                        $component, 
                        $callback, 
                        $priority, 
                        $accepted_args ) 
  {
		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args
		);

		return $hooks;
	}


  public function load_includes($plugin_dir)
  {
    foreach($this->get_includes() as $include)
    {
      include_once($plugin_dir . $include);
    }
  }
  
	/**
	 * Register the filters with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function register_filters() 
  {
		foreach ( $this->get_filters() as $hook ) 
    {
			add_filter( $hook['hook'], 
                  array( $hook['component'], 
                         $hook['callback'] ), 
                         $hook['priority'], 
                         $hook['accepted_args'] );
		}
  }

	/**
	 * Register the actions with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function register_actions() 
  {
		foreach ( $this->get_actions() as $hook ) 
    {
			add_action( $hook['hook'], 
                  array( $hook['component'], 
                         $hook['callback'] ), 
                         $hook['priority'], 
                         $hook['accepted_args'] );
		}
	}

  public function execute_starters()
  {
    foreach($this->get_starters() as $starter)
    {
      $starter->start();
    }
  }
}

interface WPModuleStarterIF
{
  public function start();
}

