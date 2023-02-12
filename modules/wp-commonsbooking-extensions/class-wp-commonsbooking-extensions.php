<?php
/**
 * Das Organisation Modul erstellt ein eigens Posttype für Organisation
 * inkl. Custom Fields.
 */
class WPCommonsBookingExtensionsModule extends WPAbstractModule 
{
	const CRON_CB_HOOK	= 'minutely_commonsbooking_hook';

  public function setup_includes($loader)
  {
    $loader->add_include('/inc/lib/commonsbooking/class-timeframe-menuactions.php');
    $loader->add_include('/inc/lib/commonsbooking/class-duplicate-timeframe.php');

    // Admin
    $loader->add_include('/admin/inc/controllers/class-commonsbooking-extensions-admincontrol.php');

    add_action( 'cbe_cron', array( $this,
                         'update_cbe'));
  }

  public function setup($loader)
  {
    $menuActions = new TimeFrameMenuActions();
    $menuActions->setup($loader);


//    $loader->add_action( WPCommonsBookingExtensionsModule::CRON_CB_HOOK,
//                         $this, 
//                         'update_cb_minute');
    $loader->add_starter(new CommonsBookingExtensionsAdminControl($this));

    // -- Set Schedule Hook (CRON tasks)
    if (!wp_next_scheduled ( 'cbe_cron' )) 
    {
      //daily | hourly | tenminutely
		  wp_schedule_event( time(), 'every_minute', 
        'cbe_cron' ); 
    }
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

    // -- Set Schedule Hook (CRON tasks)
    if (!wp_next_scheduled ( 'cbe_cron' )) 
    {
      //daily | hourly | tenminutely
		  wp_schedule_event( time(), 'hourly', 
        'cbe_cron' ); 
    }
  }

  public function module_deactivate()
  {
		wp_clear_scheduled_hook( WPCommonsBookingExtensionsModule::CRON_CB_HOOK );
  }

  public function module_uninstall()
  {
  }

  public function update_cbe()
  {
    $job = new DuplicateTimeFrame();
    $job->duplicate_backend(); 
  }

}

?>
