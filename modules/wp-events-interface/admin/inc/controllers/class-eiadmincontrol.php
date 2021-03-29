<?php

define( 'EI_VERSION', 16 );
define( 'EI_CUSTOM_DATE_RANGE_DAYS', 0 );

/**
 * Controller EIAdminControl
 * Settings page of the Events Interface.
 * It uses the UISettingsPage which use the 
 * Wordpress Settings API
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class EIAdminControl implements WPModuleStarterIF
{
  public function start() 
  {
    $mc = WPModuleConfiguration::get_instance();
    $rootmodule = $mc->get_root_module();

    $page = new UISettingsPage('events-interface-options', 
                               'Events interface');
    $page->set_submenu_page(true, $rootmodule->get_id() .'-menu');
    
    $section = $page->add_section('ei_section_one', 'Settings');
    $section->set_description(
      'Here we are able to set the calendar plugin and defaults for '.
      'retrieving events from the setted Wordpress calendar plugin. <br/> '.
      'Plugins that are using this interface are indepent of the '.
      'underlying active event calendar, so they do not have to '.
      'write code for the many different event calendar plugins '.
      'that exists in Wordpress');

    $field = $section->add_dropdownfield('ei_event_calendar', 
                                         'Calendar plugin');
    $mc = WPModuleConfiguration::get_instance();
    $module = $mc->get_module('wp-events-interface');
	  $available_feeds = $module->get_available_calendar_feeds();
    foreach($available_feeds as $feed)
    {
      $field->add_value( $feed->get_identifier(), 
                         $feed->get_description());
    }

    $section->add_textfield('ei_events_future_in_days', 
                            'Timerange to select in days');
    $section->add_textfield('ei_event_category', 
                            'Category (slug) to select');
    $field = $section->add_checkbox('ei_delete_permanently', 
                                   'Delete events permanently');
    $field->set_description('If this option is not marked, the events will be putted in the trash');
    $field = $section->add_checkbox('ei_fill_lanlon_coordinates_over_osm', 'Fill longitude und latitude automatically by OSM');
    $field->set_description('Fill longitude und latitude automatically by Open Street Maps Nominatim wenn a location is saved in the calendar plugin');

    if(!$rootmodule->are_developer_options_enabled())
    {
      $page->register();
      $this->add_saved_listener();
      return;
    }

    $section->add_textarea('ei_event_saved', 
                           'Last saved events');
    
    $field = new class('ei_event_defaultresult', 
                       'Output from interface') 
                       extends UISettingsTextAreaField
    {
      public function get_value()
      {
        $mc = WPModuleConfiguration::get_instance();
        $module = $mc->get_module('wp-events-interface');
        $events = $module->get_events_by_cat();
    
        $result = '';

        foreach ( $events as $event ) 
        {
          $result .= $event->to_text();
          $result .= '---------------------';
          $result .= PHP_EOL;
        }
        return $result;
      }
    };

    $field->set_register(false);
    $section->add_field($field);

    $page->register();
    $this->add_saved_listener();
    
  }

  private function add_saved_listener()
  {
    $mc = WPModuleConfiguration::get_instance();
    $module = $mc->get_module('wp-events-interface');
    $module->add_event_saved_listener(
      new class() implements EIEventSavedListenerIF
      {
        public function event_saved($eiEvent)
        {
          $text = 'EVENT SAVED: ' . current_time('mysql') . PHP_EOL;
          $text .= $eiEvent->to_string();
          $text .= '----------' . PHP_EOL;
          update_option( 'ei_event_saved', $text ); 
        }
      });

  }
}
