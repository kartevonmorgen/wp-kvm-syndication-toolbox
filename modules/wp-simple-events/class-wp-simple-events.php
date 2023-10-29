<?php
/*
 * The SimpleEventsModule allows one to create and manage events 
 *
 * @author     Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class WPSimpleEventsModule extends WPAbstractModule
{
  public function setup_includes($loader)
  {
    $loader->add_include("/inc/lib/simpleevents/class-wp-register-simpleevents-posttype.php");
    $loader->add_include("/inc/lib/simpleevents/class-wp-simpleevents-queryhelper.php");
  }

  public function setup($loader)
  {
    $posttype = new WPRegisterSimpleEventsPosttype($this);
    $posttype->setup($loader);
    $loader->add_starter($posttype);
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

  public function get_events($start_date, $end_date, $cat=null)
  {
    $queryHelper = new WPSimpleEventsQueryHelper($this);
    return $queryHelper->get_events($start_date, $end_date, $cat);
  }

  public function get_event_by_slug($slug)
  {
    $queryHelper = new WPSimpleEventsQueryHelper($this);
    return $queryHelper->get_event_by_slug($slug);
  }

  public function get_posttype()
  {
    return 'simple_event';
  }

  public function get_postname()
  {
    return 'Simple event';
  }

}
