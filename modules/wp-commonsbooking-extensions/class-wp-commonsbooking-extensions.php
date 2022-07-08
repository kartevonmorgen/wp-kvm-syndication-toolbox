<?php
/**
 * Das Organisation Modul erstellt ein eigens Posttype für Organisation
 * inkl. Custom Fields.
 */
class WPCommonsBookingExtensionsModule extends WPAbstractModule 
{
  public function setup_includes($loader)
  {
    $loader->add_include('/inc/lib/commonsbooking/class-timeframe-menuactions.php');
    $loader->add_include('/inc/lib/commonsbooking/class-duplicate-timeframe.php');

    // Admin
    $loader->add_include('/admin/inc/controllers/class-commonsbooking-extensions-admincontrol.php');

  }

  public function setup($loader)
  {
    $menuActions = new TimeFrameMenuActions();
    $menuActions->setup($loader);

    $loader->add_starter(new CommonsBookingExtensionsAdminControl($this));
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

}

?>
