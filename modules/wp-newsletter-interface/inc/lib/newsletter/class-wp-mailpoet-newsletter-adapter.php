<?php
/**
 * WPMailpoetNewsletterAdapter
 * used to implement a generic Adapter
 * for a Wordpress Newsletter Plugin
 *
 * @author    Sjoerd Takken
 * @copyright No Copyright.
 * @license   GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class WPMailpoetNewsletterAdapter extends WPNewsletterAdapter
{
  public function __construct()
  {
  }

  public function init()
  {
  }

  public function get_id()
  {
    return 'malipoet-newsletter';
  }

  public function get_description()
  {
    return 'Mailpoet Newsletter';
  }

  public function is_plugin_available() 
  {
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    return is_plugin_active( 'mailpoet3/mailpoet.php' );
  }

  public function update_newsletter_list()
  {
  }

  public function remove_newsletter_list()
  {
  }
}
