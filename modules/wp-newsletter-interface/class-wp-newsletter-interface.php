<?php
/*
 * The WPNewsletterInterface makes it possible to add
 * Events to a Newsletter
 *
 * @author     Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class WPNewsletterInterfaceModule extends WPAbstractModule
{
  private $_newsletterAdapterFactory;

  public function __construct()
  {
    parent::__construct('Newsletter Interface');
    $this->set_description('Das Modul kann events in ein Newsletter importieren ' . 
                           'und unterstützt mehrere Newsletter Plugins in Wordpress ');
  }

  public function setup_includes($loader)
  {
    $loader->add_include("/inc/lib/newsletter/class-wp-newsletter-action.php");
    $loader->add_include("/inc/lib/newsletter/class-wp-newsletter-adapter-factory.php");
    $loader->add_include("/inc/lib/newsletter/class-wp-newsletter-adapter.php");
    $loader->add_include("/inc/lib/newsletter/class-wp-noptin-newsletter-adapter.php");
    $loader->add_include("/inc/lib/newsletter/class-wp-mailpoet-newsletter-adapter.php");
    $loader->add_include("/inc/lib/newsletter/class-wp-register-newsletter-list-posttype.php");
    $loader->add_include("/inc/lib/newsletter/class-newsletter-list-table-columns.php");

    $loader->add_include("/admin/inc/controllers/class-wp-newsletter-interface-admincontrol.php");
  }

  public function setup($loader)
  {
    $this->_newsletterAdapterFactory = 
      new WPNewsletterAdapterFactory($this);

    $cposttype = null;
    if($this->is_newsletter_lists_support_enabled())
    {
      $cposttype = new WPRegisterNewsletterListPosttype($this);
      $cposttype->setup($loader);
      $tableColumns = new NewsletterListTableColumns($this); 
      $tableColumns->setup($loader);
    }
    
    $adapter = $this->get_current_newsletter_adapter();
    if(!empty($adapter))
    {
      $adapter->setup($loader);
    }

    if(!empty($cposttype))
    {
      $loader->add_starter($cposttype);
    }
    $loader->add_starter(
      new WPNewsletterInterfaceAdminControl($this));
  }

  public function module_activate()
  {
  }

  public function module_deactivate()
  {
  }

  public function module_uninstall()
  {
  }

  public function get_newsletter_adapter_id() 
  {
    return 'ni_newsletter_adapter';
  }

  public function get_newsletter_adapter() 
  {
    return get_option( $this->get_newsletter_adapter_id() );
  }

  public function save_newsletter_adapter( $adapter ) 
  {
    update_option( $this->get_newsletter_adapter_id(), $adapter );
  }

  public function get_available_newsletter_adapters()
  {
    return $this->_newsletterAdapterFactory->get_adapters();
  }

  /**
   * Return the activated newsletteradapter.
   * If no activated adapter is set (option ni_newsletter_adapter)
   * we return just the first Newsletter Plugin that we find, because
   * most of the time there is just one Newsletter plugin installed.
   *
   * @return WPNewsletterAdapter
   */
  public function get_current_newsletter_adapter() 
  {
    $available_adapters = $this->get_available_newsletter_adapters();
    $adapter_id = $this->get_newsletter_adapter();
    if(empty($adapter_id))
    {
      // If no adapter is set, we just take the first one
      if(!empty( $available_adapters ))
      {
        return reset($available_adapters);
      }
    }

    foreach($available_adapters as $adapter)
    {
      if($adapter->get_id() == $adapter_id)
      {
        return $adapter;
      }
    }

    // The identifier $adapter_id is invalid,
    // so we set it to an empty string
    $this->save_newsletter_adapter('');
    return null;
  }

  public function get_newsletter_lists_support_enabled_id()
  {
    return 'ni_newsletter_lists_support_enabled_id';
  }

  public function is_newsletter_lists_support_enabled()
  {
    return get_option($this->get_newsletter_lists_support_enabled_id(), false);
  }

  public function has_newsletter_list()
  {
    $adapter = $this->get_current_newsletter_adapter();
    if(empty($adapter))
    {
      return false;
    }
    return $adapter->has_newsletter_list();
  }

  public function get_number_of_days_id()
  {
    return 'ni_number_of_days_id';
  }

  public function get_default_number_of_days()
  {
    return 30;
  }

  public function get_number_of_days()
  {
    return get_option($this->get_number_of_days_id(), 
                      $this->get_default_number_of_days());
  }

  public function get_selected_category_id()
  {
    return 'ni_selected_category_id';
  }

  public function get_selected_category()
  {
    return get_option($this->get_selected_category_id());
  }

  public function get_send_new_email_body_with_events_id()
  {
    return 'ni_send_new_email_body_with_events_id';
  }

  public function is_send_new_email_body_with_events()
  {
    return get_option($this->get_send_new_email_body_with_events_id(),false);
  }

}
