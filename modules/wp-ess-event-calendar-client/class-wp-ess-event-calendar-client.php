<?php
/*
*/

// CHeck if Secure
if ( !defined( 'ESS_SECURE') ) {define( 'ESS_SECURE',((!empty($_SERVER['HTTPS']) && @$_SERVER['HTTPS'] !== 'off') || @$_SERVER['SERVER_PORT'] == 443 || stripos( @$_SERVER[ 'SERVER_PROTOCOL' ], 'https' ) === TRUE) ? TRUE : FALSE);}


class WPESSEventCalendarClientModule extends WPAbstractModule
                                     implements WPModuleStarterIF
{
  public function __construct()
  {
    parent::__construct('ESS Event Calendar client');
    $this->set_description('Das Modul kann auf einem Client ' . 
                           'benutzt werden um Veranstaltungen ' .
                           'als ESS-Feed zur Verführung ' .
                           'zu stellen. Die können dann auf ' .
                           'eine andere Webseite mit dem ' .
                           'Events feed importer ' .
                           'importiert werden über ESS');
  }

  public function setup_includes($loader)
  {
    // -- View --
    $loader->add_include('/admin/inc/views/class-ess-feedbuilder.php');

    // -- Controllers --
    $loader->add_include('/admin/inc/controllers/class-ess-feedhandler.php');
    $loader->add_include('/admin/inc/controllers/class-ess-admincontrol.php' );

    $loader->add_include('/inc/lib/ess/FeedWriter.php' );
  }

  public function setup($loader)
  {
    $feedhandler = new ESSFeedHandler();
    $feedhandler->setup($loader);

    $loader->add_starter(new ESSAdminControl());
  }

  public function start()
  {
    // Start UI Part
  }

  public function module_activate()
  {
		flush_rewrite_rules();

		if ( !current_user_can( 'activate_plugins' )) 
    {
      return;
    }

    $mc = WPModuleConfiguration::get_instance();
    $root = $mc->get_root_module();

    $plugin = isset( $_REQUEST[ 'plugin' ] ) ? $_REQUEST[ 'plugin' ] : $root->get_plugin_id();

    // Checks Permissions
    check_admin_referer( "activate-plugin_{$plugin}" );
  }

  public function module_deactivate()
  {
		if ( !current_user_can( 'activate_plugins' ) ) 
    {
      return;
    }

    $mc = WPModuleConfiguration::get_instance();
    $root = $mc->get_root_module();
    $plugin = isset( $_REQUEST[ 'plugin' ] ) ? $_REQUEST[ 'plugin' ] : $root->get_plugin_id();

    // Checks Permissions
    check_admin_referer( "deactivate-plugin_{$plugin}" );
  }

  public function module_uninstall()
  {
    if ( ! current_user_can( 'activate_plugins' ) ) 
    {
      return;
    }

    // Checks Permissions
    check_admin_referer( 'bulk-plugins' );
  }

  public function get_parent_classname()
  {
    return 'WPEventsInterfaceModule';
  }
}
