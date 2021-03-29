<?php
/*
 * The DashboardPostsModule allows one to change the view of the Desktop 
 *
 * @author     Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class WPDashboardPostsModule extends WPAbstractModule
{
  public function setup_includes($loader)
  {
    $loader->add_include("/inc/lib/dashboard/class-wp-register-dashboard-posttype.php");
    $loader->add_include("/inc/lib/dashboard/class-wp-dashboard-remover.php");
    $loader->add_include("/inc/lib/dashboard/class-wp-dashboard-metabox.php");
  }

  public function setup($loader)
  {
    $remover = new WPDashboardRemover();
    $remover->setup($loader);

    $dposttype = new WPRegisterDashboardPosttype();
    $dposttype->setup($loader);

    $args = array(
       'numberposts' => 5,
       'post_type'   => 'dpost');
    $dposts = get_posts($args);
    foreach($dposts as $dpost)
    {
      $metabox = new WPDashboardMetabox($dpost);
      $metabox->setup($loader);
    }

    $loader->add_starter($dposttype);
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
