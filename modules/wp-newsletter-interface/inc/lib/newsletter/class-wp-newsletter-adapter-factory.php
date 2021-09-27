<?php
/**
 * WPNewsletterAdapterFactory
 * Check which different Newsletter Plugins are available
 * and activated.
 * The Factory create instances for all the
 * available and active Newsletter Plugins in Wordpress.
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class WPNewsletterAdapterFactory extends WPAbstractModuleProvider 
{
  private $supported_plugins = array();
  private $load_available_newsletter_adapters = null;
  
  public function __construct($current_module)
  {
    parent::__construct($current_module);

    $this->supported_plugins = array(
      'Noptin'); 
      //'Mailpoet');
  }

  /**
   * Load the available calendar feeds
   *
   */
  private function create_available_newsletter_adapters() 
  {
    $this->load_available_newsletter_adapters = array();
	  foreach ( $this->supported_plugins as $plugin ) 
    {
      $class_name = 'WP' . $plugin . 'NewsletterAdapter';
      if ( class_exists( $class_name ) ) 
      {
	      $adapter = new $class_name($this->get_current_module());
        if($adapter->is_plugin_available())
        {
          $adapter->init();
          array_push($this->load_available_newsletter_adapters, 
                     $adapter);
        }
		  }
    }
  }

  public function get_adapters() 
  {
    if( !isset( $this->load_available_newsletter_adapters ))
    {
      $this->create_available_newsletter_adapters();
    }
    return $this->load_available_newsletter_adapters;
  }

  public function get_adapter( $id) 
  {
    foreach ( $this->get_adapters() as $adapter ) 
    {
      if ( $id == $feed->get_id() )
      {
        return $adapter;
      }
    }
    return null;
  }
}
