<?php
/**
 * WPNewsletterAdapter
 * used to implement a generic Adapter
 * for a Wordpress Newsletter Plugin
 *
 * @author    Sjoerd Takken
 * @copyright No Copyright.
 * @license   GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
abstract class WPNewsletterAdapter extends WPAbstractModuleProvider
{
  private $_id;
  private $_description;

  public function init()
  {
  }

  public function setup($loader)
  {
  }

  public abstract function get_id();

  public abstract function get_description();

  public abstract function is_plugin_available();

  public function has_newsletter_list()
  {
    return false;
  }

  public abstract function update_newsletter_list();

  public abstract function remove_newsletter_list();
}
