<?php
/**
  * Controller SSAdminControl
  * Settings page of the ESS Feed Server.
  *
  * @author  	Sjoerd Takken
  * @copyright 	No Copyright.
  * @license   	GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
  */
class SSAdminControl extends UIAbstractAdminControl 
                     implements WPModuleStarterIF
{
  public function start() 
  {
    $rootmodule = $this->get_root_module();
    $thismodule = $this->get_current_module();

    $page = new UISettingsPage('events-feed-importer', 
                               'Events feed importer settings');
    $page->set_submenu_page(true, $rootmodule->get_id() .'-menu');
    $section = $page->add_section('ss_section_one', 'General settings');
    $section->set_description(
       "This section defines the way the imported feeds " . 
       "events will appears in your event dashboard.");

    $field = $section->add_checkbox($thismodule->get_publish_directly_id(), 
                                    'Publish events directly');
    $field->set_description('If events are imported, then publish them directly on the website, otherwise the state will be pending ');

    $field = $section->add_checkbox($thismodule->get_backlink_enabled_id(), 
                                    'Add link from source');
    $field->set_description('Add at the bottom of the events description a link to the website where the event is coming from');

    $field = $section->add_textfield($thismodule->get_category_prefix_id(), 
                                    'Strip prefix from category');
    $field->set_description('Strip this prefix from imported categories');

    $field = $section->add_textarea('ss_cron_message', 
                                    'Last message eventscron');
    $field->set_description('Last message from daily cron job to retrieve events');

    $section = $page->add_section('ss_section_two', 'Limits of imported events');
    $section->set_description(
       "This section defines the maximum of events that " . 
       "will be imported by count and period.");
    
    $field = $section->add_textfield($thismodule->get_max_recurring_count_id(), 
                                    'Max. recurring events');
    $field->set_description('Maximum number of imported recurring events ');
    $field->set_defaultvalue(10);

    $field = $section->add_textfield($thismodule->get_max_periodindays_id(), 
                                    'Max. period in Days');
    $field->set_description('Maximum period of time that will be used to ' . 
                            'import events ');
    $field->set_defaultvalue(356);

    $field = $section->add_textfield($thismodule->get_max_events_pro_feed_id(),
                                       'Maximum events pro Feed');
    $field->set_description('Maximum number of events that will be imported ' . 
                              'pro feed (saves waiting time), default -1, ' .
                              'imports all events ');
    $field->set_defaultvalue(-1);

    $page->register();
  }
}
