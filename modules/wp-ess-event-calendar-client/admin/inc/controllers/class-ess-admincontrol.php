<?php
/**
  * Controller ESSAdminControl
  * Settings page of the ESS Feed Client.
  * It uses the Interface Events API to retrieve the events
  * from the active event calendar and then ESSFeedBuilder
  * generate a xml ess feed out of it.
  * This xml ess feed will be showed in the UI to see direct what happens.
  *
  * @author  	Sjoerd Takken
  * @copyright 	No Copyright.
  * @license   	GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
  */
class ESSAdminControl implements WPModuleStarterIF
{
  public function __construct() 
  {
  }

  public function start() 
  {
    $mc = WPModuleConfiguration::get_instance();
    $rootmodule = $mc->get_root_module();

    $page = new UISettingsPage('ess-event-calendar-client', 
                               'ESS feed client settings');
    $page->set_submenu_page(true, $rootmodule->get_id() . '-menu');
    $section = $page->add_section('ess_section_one', 'Header elements');
    $section->set_description(
      'This section defines the header elements of your feeds. '.
      'Those elements will be read by search engines to identify'. 
      'the origin of the events.');

    $section->add_textfield('ess_feed_title', 
                            'Feed title');
    $section->add_textfield('ess_feed_rights', 
                            'Feed rights');
    $section->add_textfield('ess_feed_website', 
                            'Feed website');

    $section = $page->add_section('ess_section_two', 'Display organizer');
    $section->set_description(
      'Display, in the feed, the coordinate of the event organizer '.
      '(will be the same for all the events).');

    $section->add_checkbox('ess_owner_active', 
                           'Owner active');
    $section->add_textfield('ess_owner_name', 
                            'Owner name');
    $section->add_textfield('ess_owner_company', 
                            'Owner company');
    $section->add_textfield('ess_owner_address', 
                            'Owner address');
    $section->add_textfield('ess_owner_zip', 
                            'Owner zip');
    $section->add_textfield('ess_owner_city', 
                            'Owner city');
    $section->add_textfield('ess_owner_state', 
                            'Owner state');
    $section->add_textfield('ess_owner_country', 
                            'Owner country');
    $section->add_textfield('ess_owner_website', 
                            'Owner website');
    $section->add_textfield('ess_owner_phone', 
                            'Owner phone');
    
    $section = $page->add_section('ess_section_three', 'Output ESS Feed');
    $section->set_description('Displays the output of the feed ');
    $field = new class('ess_output_result', 'Output for ESS Feed') 
       extends UISettingsTextAreaField
    {
      public function get_value()
      {
        try
        {
          $mc = WPModuleConfiguration::get_instance();
          $ei_module = $mc->get_module('wp-events-interface');
          $events = $ei_module->get_events_by_cat();
          $feedBuilder = new ESSFeedBuilder();
          return $feedBuilder->generateFeed($events);
        }
        catch(Exception $ex)
        {
          return $ex->getMessage() . PHP_EOL . 
            $ex->getTraceAsString();
        }
      }   
    };
    $field->set_register(false);
    $section->add_field($field);

    $page->register();
  }
}
