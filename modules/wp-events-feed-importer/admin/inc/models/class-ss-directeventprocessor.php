<?php
/**
  * Controller SSDirectImportProcessor
  * Process the imported feeds directly, for
  * example to pass events further to another Process or website
  * without saving it in Wordpress.
  *
  * @author     Sjoerd Takken
  * @copyright 	No Copyright.
  * @license   	GNU/GPLv2, see http://www.gnu.org/licenses/gpl-2.0.html
  * @link	      https://github.com/kartevonmorgen
  */
class SSDirectEventProcessor extends SSAbstractEventProcessor
{
  public function process($eiEvents)
  {
    $logger = $this->get_logger();
    $importer = $this->get_importer();

    $updated_event_ids = array();

    foreach($eiEvents as $eiEvent)
    {
      $logger->add_newline();
      $logger->add_line('Process Event ' .$eiEvent->get_uid()); 
      $logger->set_prefix('  ');

      // Check if the Event has been changed
      // only by changes we save it.
      // This prevents not needed saves and updates 
      // to the Karte von Morgen
      $mc = WPModuleConfiguration::get_instance();
      $eiInterface = $mc->get_module('wp-events-interface');
      $oldEiEvent = $eiInterface->get_event_by_uid(
                      $eiEvent->get_uid());
      if(empty($oldEiEvent))
      {
        $logger->add_line('event does not exist'); 
      }
      else
      {
        $result = $oldEiEvent->equals_by_content($eiEvent);
        if($result->is_true())
        {
          $logger->add_line('events are equal, ' .
                            'so we do NOT update'); 
          array_push( $updated_event_ids, 
                      $oldEiEvent->get_event_id());
          continue;
        }
        $logger->add_line('event has been changed (' . 
          $result->get_message() . ') so we save it'); 
      }

      // Only save if we have changes
      $result = $eiInterface->save_event($eiEvent);
      if( $result->has_error() )
      {
        $logger->add_line('save_event gives an ERROR (' . 
          $result->get_error() . ') '); 
        $this->set_error($result->get_error());
        if($importer->is_echo_log())
        {
          $logger->echo_log();
        }
        $logger->save();
        return;
      }

      if( !empty( $result->get_event_id() ))
      {
        array_push( $updated_event_ids, $result->get_event_id());
      }
      if($importer->is_echo_log())
      {
        $logger->echo_log();
      }
      $logger->save();
    }

    $logger->remove_prefix();
    $logger->add_newline();
    $logger->add_line('process events finished: save the new ' .
                      'feed status');
    if($importer->is_echo_log())
    {
      $logger->echo_log();
    }
    $logger->save();

    // Save the eventids that were already there
    $last_event_ids = $this->get_feed_eventids();

    $this->save_feed($updated_event_ids);

    // We check if some events are 
    // no longer in the feed, if so, we delete these events.
    if(empty($last_event_ids))
    {
      $logger->add_line('-- nothing to delete, ' . 
                        'update feed finished ---');
      if($importer->is_echo_log())
      {
        $logger->echo_log();
      }
      $logger->save();
      return;
    }

    $logger->add_line('delete no longer updated events ');
    $logger->add_prefix('  ');

    // Check if auto-deletion is disabled
    $importer = $this->get_importer();
    $thismodule = $importer->get_current_module();
    if ($thismodule->is_disable_auto_delete()) 
    {
      $logger->add_line('Automatic event deletion is disabled, skipping deletion of old events');
      $logger->remove_prefix();
      $logger->add_line('-- update feed finished ---');
      if($importer->is_echo_log()) {
        $logger->echo_log();
      }
      $logger->save();
      return;
    }

    foreach($last_event_ids as $last_event_id)
    {
      if(empty($last_event_id))
      {
        continue;
      }

      if(in_array($last_event_id, $updated_event_ids))
      {
        continue;
      }

      $logger->add_line('delete event (id=' . 
                        $last_event_id. ')');
      $mc = WPModuleConfiguration::get_instance();
      $eiInterface = $mc->get_module('wp-events-interface');
      $eiInterface->delete_event_by_event_id($last_event_id);
    }
    $logger->remove_prefix();
    $logger->add_line('-- update feed finished ---');
    if($importer->is_echo_log())
    {
      $logger->echo_log();
    }
    $logger->save();
  }
          
  // == SAVE FEED ===
  // Only save the Feed if, 
  // at least, one event have been saved.
  private function save_feed($updated_event_ids)
  {
    $importer = $this->get_importer();
    $feed_id = $this->get_feed_id();
    if(empty($feed_id))
    {
      $this->set_error("Feed Id is 0");
      return;
    }
    $lastupdate = get_date_from_gmt(date("Y-m-d H:i:s"));

    update_post_meta($feed_id, 'ss_feed_title', 
                     $importer->get_feed_title());
    update_post_meta($feed_id, 'ss_feed_uuid', 
                     $importer->get_feed_uuid());
    update_post_meta($feed_id, 'ss_feed_lastupdate', 
                     $lastupdate);
    update_post_meta($feed_id, 'ss_feed_eventids', 
                     implode(',',$updated_event_ids ));
  }

  private function get_feed_eventids()
  {
    $importer = $this->get_importer();
    $au = new PHPArrayUtil();

    $result = explode(',', 
     $importer->get_feed_meta('ss_feed_eventids'));
    return $au->remove_empty_entries($result);
  }
}
