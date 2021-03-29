<?php

if ( !defined( 'EVENTS_SS_SECURE' )) 
{
  define( 'EVENTS_SS_SECURE',((!empty($_SERVER['HTTPS']) && @$_SERVER['HTTPS'] !== 'off') || @$_SERVER['SERVER_PORT'] == 443 || stripos( @$_SERVER[ 'SERVER_PROTOCOL' ], 'https' ) === TRUE) ? TRUE : FALSE);
}

/**
 * This Module loads events from a Feed into the currently 
 * selected Events Calendar in Wordpress.
 *
 * Supported Feeds:
 * - ESS Feed
 *     ESS-Feeds are like RSS-Feeds, 
 *     but especially made for events 
 *     On GitHub: https://github.com/essfeed
 *     On Youtube: https://www.youtube.com/watch?v=OGi0U3Eqs6E
 * - iCal Feed
 *     ICal Feeds are also supported.
 *     Recurring Events are converted to single events.
 */
class WPEventsFeedImporterModule extends WPAbstractModule 
{
  private $_ssfeeds;

  public function setup_includes($loader)
  {
    // Action for updating the feed
    $loader->add_include('/inc/lib/feed/class-ss-updatefeed.php');

    // -- MODELS
    $loader->add_include('/admin/inc/models/class-ss-notices.php');
    $loader->add_include('/admin/inc/models/class-ss-importtype.php');
    $loader->add_include('/admin/inc/models/class-ss-importerfactory.php');
    $loader->add_include('/admin/inc/models/class-ss-feeds.php');
    $loader->add_include('/admin/inc/models/class-ss-abstractimport.php');
    $loader->add_include('/admin/inc/models/class-ss-essimport.php');
    $loader->add_include('/admin/inc/models/class-ss-icalimport.php');

    // -- CONTROLLERS
    $loader->add_include('/admin/inc/controllers/class-ss-admincontrol.php');
    $loader->add_include('/admin/inc/controllers/class-ss-io.php');
  }

  public function setup($loader)
  {
    $ssnotices = new SSNotices();
    $ssnotices->setup($loader);

    $ssio = new SS_IO($ssnotices);
    $ssio->setup($loader);

    $ssfeeds = new SSFeeds();
    $ssfeeds->setup($loader);
    $loader->add_starter($ssfeeds);
    $this->set_ssfeeds($ssfeeds);

    $loader->add_action( SS_IO::CRON_EVENT_HOOK, 
                         $this, 
                         'update_feeds_daily');

    // Start UI Settings Part
    $loader->add_starter( new SSAdminControl());
  }

	public function module_activate()
	{
		flush_rewrite_rules();

		if ( !current_user_can( 'activate_plugins' ) ) 
    {
      return;
    }

    $plugin = $_REQUEST[ 'plugin' ];
    check_admin_referer( "activate-plugin_{$plugin}" );

    $role = get_role( 'administrator' );
    $role->add_cap( 'manage_event_feeds');
    $role->add_cap( 'manage_other_event_feeds');
    $role = get_role( 'editor' );
    $role->add_cap( 'manage_event_feeds');
    $role = get_role( 'author' );
    $role->add_cap( 'manage_event_feeds');

    // -- Set Schedule Hook (CRON tasks)
    if (!wp_next_scheduled ( SS_IO::CRON_EVENT_HOOK )) 
    {
      //daily | hourly | tenminutely
		  wp_schedule_event( time(), 'daily', 
        SS_IO::CRON_EVENT_HOOK ); 
    }
	}

	public function module_deactivate()
	{
		if ( !current_user_can( 'activate_plugins' ) ) 
    {
      return;
    }
    return;

    //$plugin = $this->get_plugin();;
    $plugin = $_REQUEST[ 'plugin' ];
    check_admin_referer( "deactivate-plugin_{$plugin}" );

		// -- Remove Schedule Hook (CRON tasks)
		wp_clear_scheduled_hook( SS_IO::CRON_EVENT_HOOK );
	}

	public function module_uninstall()
  {
    if ( ! current_user_can( 'activate_plugins' ) ) 
    {
      return;
    }

    check_admin_referer( 'bulk-plugins' );

		// -- Remove Schedule Hook (CRON tasks)
		wp_clear_scheduled_hook( SS_IO::CRON_EVENT_HOOK );
  }

  private function set_ssfeeds($ssfeeds)
  {
    $this->_ssfeeds = $ssfeeds;
  }

  private function get_ssfeeds()
  {
    return $this->_ssfeeds;
  }

  function update_feeds_daily()
  {
    $ssfeeds = $this->get_ssfeeds();
    if(!empty($ssfeeds))
    {
      $ssfeeds->update_feeds_daily();
    }
  }

  public function get_publish_directly_id()
  {
    return 'ss_publish_directly';
  }

  public function is_publish_directly()
  {
    return get_option($this->get_publish_directly_id(), false);
  }

  public function get_backlink_enabled_id()
  {
    return 'ss_backlink_enabled';
  }

  public function is_backlink_enabled()
  {
    return get_option($this->get_backlink_enabled_id(), false);
  }

  public function get_category_prefix_id()
  {
    return 'ss_category_prefix';
  }

  public function get_category_prefix()
  {
    return get_option($this->get_category_prefix(), '');
  }

  public function get_max_recurring_count_id()
  {
    return 'ss_max_recurring_count';
  }

  public function get_max_recurring_count()
  {
    return get_option($this->get_max_recurring_count_id(), 10);
  }
  
  public function get_max_periodindays_id()
  {
    return 'ss_max_periodindays';
  }

  public function get_max_periodindays()
  {
    return get_option($this->get_max_periodindays_id(), 356);
  }
  
  public function get_max_events_pro_feed_id()
  {
    return 'ss_max_events_pro_feed';
  }

  public function get_max_events_pro_feed()
  {
    return get_option($this->get_max_events_pro_feed_id(), -1);
  }
}
