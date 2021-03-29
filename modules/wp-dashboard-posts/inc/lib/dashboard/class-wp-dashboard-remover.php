<?php

class WPDashboardRemover
{
  public function __construct()
  {
  }

  public function setup($loader)
  {
    $loader->add_action('wp_dashboard_setup', $this, 'do_remove');
  }

  function do_remove()
  {
    // Remove Welcome panel
    remove_action( 'welcome_panel', 'wp_welcome_panel' );
  
    // Remove the rest of the dashboard widgets
    remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
    remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
    remove_meta_box( 'health_check_status', 'dashboard', 'normal' );
    remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
    remove_meta_box( 'dashboard_activity', 'dashboard', 'normal');
  }
}
