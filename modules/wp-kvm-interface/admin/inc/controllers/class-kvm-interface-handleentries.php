<?php

/**
 * Create, Update and Get Entries from the Karte von Morgen
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class KVMInterfaceHandleEntries extends WPAbstractModuleProvider
{
  public CONST KVM_ENTRY_ID = 'kvm_entry_id';

  private $entriesApi;

  public function setup($loader)
  {
    // After all Plugins are loaded we start to deal with events.
    $loader->add_filter( 'wp_loaded', $this, 'start' );
  }

  public function start() 
  {
    $module = $this->get_current_module();
    $this->entriesApi = new OpenFairDBEntriesApi($module);
  }

  public function save_entry($wpEntry)
  {
    $module = $this->get_current_module();
    $module->update_config();
    $api = $this->getEntriesApi();

    $id = $wpEntry->get_kvm_id();

    $wpLocation = $wpEntry->get_location();
    if(empty($wpLocation))
    {
      $this->handleOFDBException(
        'Hochladen zu der Karte von Morgen '.
        'geht nicht, das Ort ist leer (= NULL), ',
        $wpEntry,
        $id,
        null);
      return $id;
    }

    $wpLocationHelper = new WPLocationHelper();
    $wpLocationHelper->fill_by_osm_nominatim($wpLocation);

    if(empty($wpLocation->get_lat()) ||
       empty($wpLocation->get_lon()))
    {
      $this->handleOFDBException(
        'Hochladen zu der Karte von Morgen '.
        'geht nicht, die Adresse ist nicht richtig, '.
        'keine Koordinaten gefunden für die Adresse.',
        $wpEntry,
        $id,
        null);
      return $id;
    }

    if(empty($id))
    {
      try
      {
        $module = $this->get_current_module();
        $entry = new KVMEntry($module);
        $entry->fill_entry($wpEntry);
        $id = $api->entriesPost($entry);
        $id = str_replace('"', '', $id);
        $wpEntry->set_kvm_id($id);
      }
      catch(OpenFairDBApiException $e)
      {
        $this->handleOFDBException(
          'entriesPut',
          $wpEntry,
          $id,
          $e);
        return $id;
      }
    }
    else
    {
      $entries = $api->entriesGet(array($id));
      $entryFromKVM = reset($entries);
      if(empty($entryFromKVM))
      {
        $this->handleOFDBException(
          'Hochladen zu der Karte von Morgen '.
          'geht nicht, die KVM Entry Id existiert nicht.',
          $wpEntry,
          $id,
          null);
        return '';
      }

      try
      {
        $entry = $entryFromKVM;
        $entry->fill_entry($wpEntry);
        $entry->set_version(intval($entryFromKVM->get_version()) + 1);
        $api->entriesPut($entry, $id); 
      }
      catch(OpenFairDBApiException $e)
      {
        $this->handleOFDBException(
          'entriesPut',
          $wpEntry,
          $id,
          $e);
        return $id;
      }
    }

    $this->handleOFDBException(
      'Status Okey',
      $wpEntry,
      $id,
      null);
    return $id;
  }


  /**
   * Method for Testing
   */
  public function get_entries()
  {
    $module = $this->get_current_module();
    $module->update_config();
    $api = $this->getEntriesApi();
    $kvmentries = $api->searchGet('0,0,50,50',null,null,
                          null,null,null, 10); 
    $ids = array();
    foreach($kvmentries as $kvmentry)
    {
      array_push($ids, $kvmentry->get_id());
    }
    return $this->get_entries_by_ids($ids);
  }

  public function get_entries_by_ids($ids)
  {
    try
    {
      $organisations = array();
      $api = $this->getEntriesApi();
      $entries = $api->entriesGet($ids);
      foreach($entries as $entry)
      {
        array_push($organisations, $entry->create_organisation());
      }
      return $organisations;
    }
    catch(OpenFairDBApiException $e)
    {
      if($e->getCode() == 404)
      {
        echo 'OpenFairDB entriesGet, ids not found' . 
          implode(',', $ids);
        return array();
      }
      else
      {
        throw $e;
      }
    }
  }

  public function getEntriesApi()
  {
    return $this->entriesApi;
  }

  public function handleOFDBException($msg, 
                                      $wpEntry,
                                      $kvm_id,
                                      $e)
  {
    if(empty($wpEntry->get_id()))
    {
      return;
    }

    $logger = new PostMetaLogger(
      $wpEntry->get_type()->get_id() . '_kvm_log',
      $wpEntry->get_id());

    $logger->add_date();

    $logger->add_line($wpEntry->get_type()->get_title() . ' hochladen');
    $logger->add_line($wpEntry->get_type()->get_title() . ' Name: ' . 
              $wpEntry->get_name() . 
              '(' . $wpEntry->get_id() . ')'); 
    $logger->add_line($kvm_id);
    $logger->add_line('Bericht: ' . $msg);
    if( ! empty($e ))
    {
      $logger->add_line('Exception: ');
      $logger->add_line($e->getTextareaMessage());
    }

    $logger->save();
  }
}
