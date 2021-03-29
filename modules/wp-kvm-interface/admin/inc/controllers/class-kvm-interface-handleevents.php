<?php

/**
 * Update and Read Events from the Karte von Morgen 
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class KVMInterfaceHandleEvents 
{
  public CONST KVM_EVENT_ID = 'kvm_event_id';
  public CONST KVM_UPLOAD = 'organisation_kvm_upload';

  private $eventsApi;
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
    $mc = WPModuleConfiguration::get_instance();
    $eiInterface = $mc->get_module('wp-events-interface');
    $eiInterface->add_event_saved_listener( 
      new class() implements EIEventSavedListenerIF
      {
        
        public function event_saved($eiEvent)
        {
          $mc = WPModuleConfiguration::get_instance();
          $instance = $mc->get_module('wp-kvm-interface');
          $instance->event_saved($eiEvent);
        }
      });

    $eiInterface->add_event_deleted_listener( 
      new class() implements EIEventDeletedListenerIF
      {
        
        public function event_deleted($eiEvent)
        {
          $mc = WPModuleConfiguration::get_instance();
          $instance = $mc->get_module('wp-kvm-interface');
          $instance->event_deleted($eiEvent);
        }
      });

    $this->eventsApi = new OpenFairDBEventsApi(
      $this->get_parent()->get_client(),
      $this->get_parent()->get_config());

  }

  public function event_saved($eiEvent)
  {
    $post_status = $eiEvent->get_post_status();
    if(empty($post_status))
    {
      return;
    }

    if($post_status === 'draft' ||
      $post_status === 'pending')
    {
      // delete the event if it is modified 
      // to draft or pending
      // so it is not visible anymore
      // on the Karte von Morgen
      $this->event_deleted($eiEvent);
      return;
    }

    if($post_status !== 'publish')
    {
      // Only update if the post is published
      $this->handleOFDBException(
        'Hochladen zu der Karte von Morgen '.
        'geht nicht, die Veranstaltung ' . 
        'ist nicht Veröffentlicht.',
        $eiEvent,
        $id,
        null);
      return;
    }

    $meta_id = self::KVM_EVENT_ID;
    // By recurring Events it can happen that
    // we have multiple Events in KVM and one
    // post in the event calendar.
    // So we have an instance_id where we can 
    // get the right instance for one event.
    if(!empty($eiEvent->get_event_instance_id()))
    {
      $meta_id = self::KVM_EVENT_ID . '_' . 
        $eiEvent->get_event_instance_id();
    }
    $id = get_post_meta($eiEvent->get_post_id(), 
                        $meta_id, 
                        true);

    $this->get_parent()->update_config();
    $api = $this->getEventsApi();

    $wpLocation = $eiEvent->get_location();
    if(empty($wpLocation))
    {
      // If the Event has no location, we try
      // to get the location of the organisation
      // because a lot of online events do not have
      // a location
      if( class_exists('UploadWPOrganisationToKVM'))
      {
        $iske = new UploadWPOrganisationToKVM();
        $organisation_id = get_user_meta($eiEvent->get_owner_user_id(), 'organisation_id', true);
        $ipost = get_post($organisation_id);
        $ipost_meta = get_post_meta($organisation_id);
        $wpLocation = $iske->create_location($organisation_id, $ipost, $ipost_meta);
        $eiEvent->set_location($wpLocation);
      }
    }

    if(empty($wpLocation))
    {
      $this->handleOFDBException(
        'Hochladen zu der Karte von Morgen '.
        'geht nicht, die Addresse ist ' . 
        'nicht bekannt (location is null)',
        $eiEvent,
        $id,
        null);
      return;
    }

    if(empty($wpLocation->get_lat()) ||
       empty($wpLocation->get_lon()))
    {
      $this->handleOFDBException(
        'Hochladen zu der Karte von Morgen '.
        'geht nicht, die Adresse (' . 
        $wpLocation->get_name() . ')' . 
        ' ist nicht richtig, '.
        'keine Koordinaten gefunden für die Adresse.',
        $eiEvent,
        $id,
        null);
      return;
    }


    if(empty($id))
    {
      try
      {
        $id = $api->eventsPost($eiEvent);
        $id = str_replace('"', '', $id);
        update_post_meta($eiEvent->get_post_id(),
                         $meta_id, 
                         $id);
      }
      catch(OpenFairDBApiException $e)
      {
        $this->handleOFDBException(
          'eventsPut failed',
          $eiEvent,
          '',
          $e);
        return;
      }
    }
    else
    {
      $id = str_replace('"', '', $id);
      try
      {
        $api->eventsPut($eiEvent, $id);
      }
      catch(OpenFairDBApiException $e)
      {
        $this->handleOFDBException(
          'eventsPut failed',
          $eiEvent,
          $id,
          $e);
        return;
      }
    }
    $this->handleOFDBException(
      'Event Hochladen, Status Okey',
      $eiEvent,
      $id,
      null);
    return;
  }

  public function event_deleted($eiEvent)
  {
    if(empty($eiEvent))
    {
      return;
    }

    $meta_id = self::KVM_EVENT_ID;
    
    // By recurring Events it can happen that
    // we have multiple Events in KVM and one
    // post in the event calendar.
    // So we have an instance_id where we can 
    // get the right instance for one event.
    if(!empty($eiEvent->get_event_instance_id()))
    {
      $meta_id = self::KVM_EVENT_ID . '_' . 
        $eiEvent->get_event_instance_id();
    }

    $id = get_post_meta($eiEvent->get_post_id(), 
                        $meta_id, 
                        true);

    $this->get_parent()->update_config();
    $api = $this->getEventsApi();

    if(empty($id))
    {
      return;
    }

    $id = str_replace('"', '', $id);

    try
    {
      $api->eventsDelete($id);
    }
    catch(OpenFairDBApiException $e)
    {
      $this->handleOFDBException(
        'Event Deleted failed',
        $eiEvent,
        $id,
        $e);
    }
    delete_post_meta($eiEvent->get_post_id(),
                     $meta_id);

    $this->handleOFDBException(
      'Event Deleted, Status Okey',
      $eiEvent,
      $id,
      null);
  }

  public function get_events()
  {
    $this->get_parent()->update_config();
    $api = $this->getEventsApi();
    return $api->eventsGet(null, 10); 
  }

  public function getEventsApi()
  {
    return $this->eventsApi;
  }

  public function handleOFDBException($msg, 
                                      $eiEvent,
                                      $kvm_id,
                                      $e)
  {
    if(empty($eiEvent->get_post_id()))
    {
      return;
    }

    $logger = new PostMetaLogger(
      'event_kvm_log',
      $eiEvent->get_post_id());

    $logger->add_date();

    $logger->add_line('Veranstaltung hochladen/entfernt');
    $logger->add_line('Titel: ' . 
              $eiEvent->get_title() . 
              '(postid=' . $eiEvent->get_post_id() .  
              ', eventid=' . $eiEvent->get_event_id() . ')'); 
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
