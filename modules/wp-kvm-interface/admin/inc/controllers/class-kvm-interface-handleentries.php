<?php

/**
 * Create, Update and Get Entries from the Karte von Morgen
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class KVMInterfaceHandleEntries 
{
  public CONST KVM_ENTRY_ID = 'kvm_entry_id';

  private $entriesApi;
  private $parent;

  public function __construct($parent) 
  {
    $this->parent = $parent;
  }

  public function get_parent()
  {
    return $this->parent;
  }

  public function setup($loader)
  {
    // After all Plugins are loaded we start to deal with events.
    $loader->add_filter( 'wp_loaded', $this, 'start' );
  }

  public function start() 
  {
    $this->entriesApi = new OpenFairDBEntriesApi(
      $this->get_parent()->get_client(),
      $this->get_parent()->get_config());

  }

  public function save_entry($wpOrganisation)
  {
    $this->get_parent()->update_config();
    $api = $this->getEntriesApi();

    $id = $wpOrganisation->get_kvm_id();

    $wpLocation = $wpOrganisation->get_location();
    if(empty($wpLocation))
    {
      $this->handleOFDBException(
        'Hochladen zu der Karte von Morgen '.
        'geht nicht, das Ort ist leer (= NULL), ',
        $wpOrganisation,
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
        $wpOrganisation,
        $id,
        null);
      return $id;
    }

    if(empty($id))
    {
      try
      {
        $entry = new KVMEntry();
        $entry->fill_entry($wpOrganisation);
        $id = $api->entriesPost($entry);
        $id = str_replace('"', '', $id);
        $wpOrganisation->set_kvm_id($id);
      }
      catch(OpenFairDBApiException $e)
      {
        $this->handleOFDBException(
          'entriesPut',
          $wpOrganisation,
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
          $wpOrganisation,
          $id,
          null);
        return '';
      }

      try
      {
        $entry = $entryFromKVM;
        $entry->fill_entry($wpOrganisation);
        $entry->set_version(intval($entryFromKVM->get_version()) + 1);
        $api->entriesPut($entry, $id); 
      }
      catch(OpenFairDBApiException $e)
      {
        $this->handleOFDBException(
          'entriesPut',
          $wpOrganisation,
          $id,
          $e);
        return $id;
      }
    }

    $this->handleOFDBException(
      'Status Okey',
      $wpOrganisation,
      $id,
      null);
    return $id;
  }


  /**
   * Method for Testing
   */
  public function get_entries()
  {
    $this->get_parent()->update_config();
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
                                      $wpOrganisation,
                                      $kvm_id,
                                      $e)
  {
    if(empty($wpOrganisation->get_id()))
    {
      return;
    }

    $logger = new PostMetaLogger(
      'organisation_kvm_log',
      $wpOrganisation->get_id());

    $logger->add_date();

    $logger->add_line('Organisation hochladen');
    $logger->add_line('Organisation Name: ' . 
              $wpOrganisation->get_name() . 
              '(' . $wpOrganisation->get_id() . ')'); 
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
