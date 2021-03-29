<?php
/*
 * The Interface takes care of saving events and entries 
 * from Wordpress into the Karte von Morgen. . 
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class WPKVMInterfaceModule extends WPAbstractModule
                           implements WPModuleStarterIF
{
  private $handleEvents;
  private $handleEntries;
  private $config;
  private $client;

  public function setup_includes($loader)
  {
    // -- OpenFairDB API --
    $loader->add_include("/inc/lib/openfairdb/entities/class-kvm-entry.php");
    $loader->add_include("/inc/lib/openfairdb/api/AbstractOpenFairDBApi.php");
    $loader->add_include("/inc/lib/openfairdb/api/OpenFairDBEventsApi.php");
    $loader->add_include("/inc/lib/openfairdb/api/OpenFairDBEntriesApi.php");
    $loader->add_include("/inc/lib/openfairdb/OpenFairDBApiException.php");
    $loader->add_include("/inc/lib/openfairdb/OpenFairDBConfiguration.php");


    // -- Controllers --
    $loader->add_include("/admin/inc/controllers/class-kvm-interface-admincontrol.php" );
    $loader->add_include("/admin/inc/controllers/class-kvm-interface-handleevents.php" );
    $loader->add_include("/admin/inc/controllers/class-kvm-interface-handleentries.php" );
  }

  public function setup($loader)
  {
    $this->handle_events = new KVMInterfaceHandleEvents($this);
    $this->handle_events->setup($loader);

    $this->handle_entries = new KVMInterfaceHandleEntries($this);
    $this->handle_entries->setup($loader);

    $loader->add_starter($this);
    $loader->add_starter(new KVMInterfaceAdminControl());
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

  public function start()
  {
    $this->config = OpenFairDBConfiguration::getDefaultConfiguration();

    $mc = WPModuleConfiguration::get_instance();
    $eiInterface = $mc->get_module('wp-events-interface');
    $eiInterface->register_for_kartevonmorgen();
  }

  public function save_entry($wpOrganisation)
  {
    return $this->get_handle_entries()->save_entry(
      $wpOrganisation);
  }

  /**
   * return: array of WPOrganisation
   */
  public function get_entries()
  {
    return $this->get_handle_entries()->get_entries();
  }

  /**
   * return: array of WPOrganisation
   */
  public function get_entries_by_ids($ids)
  {
    return $this->get_handle_entries()->get_entries_by_ids(
      $ids);
  }

  public function event_saved($eiEvent)
  {
    return $this->get_handle_events()->event_saved($eiEvent);
  }

  public function event_deleted($eiEvent)
  {
    return $this->get_handle_events()->event_deleted($eiEvent);
  }

  public function get_events()
  {
    return $this->get_handle_events()->get_events();
  }

  public function update_config()
  {
    $config = $this->get_config();
    $config->setHost( get_option('kvm_interface_fairdb_url'));
    $config->setAccessToken( get_option('kvm_access_token'));
  }
  
  private function get_handle_entries()
  {
    return $this->handle_entries;
  }

  private function get_handle_events()
  {
    return $this->handle_events;
  }

  public function get_config()
  {
    return $this->config;
  }

  public function get_client()
  {
    if(empty($this->client))
    {
      $this->client = new WordpressHttpClient();
    }
    return $this->client;
  }

}
