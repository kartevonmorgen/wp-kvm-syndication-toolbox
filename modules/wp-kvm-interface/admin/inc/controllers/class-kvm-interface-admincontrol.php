<?php

/**
 * Controller Karte von Morgen Interface AdminControl
 * Settings page of the Karte von Morgen Interface.
 * It uses the UISettingsPage which use the 
 * Wordpress Settings API
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class KVMInterfaceAdminControl extends UIAbstractAdminControl 
                               implements WPModuleStarterIF
{
  public function start() 
  {
    $rootmodule = $this->get_root_module();
    $module = $this->get_current_module();

    $page = new UISettingsPage('kvm-interface', 
                               'Karte von Morgen');
    $page->set_submenu_page(true, $rootmodule->get_id() .'-menu');

    $section = $page->add_section('kvm_section_one', 'Settings');
    $section->set_description(
      'The Karte von Morgen Interface allows us to automatically '.
      'upload events to the Karte von Morgen. <br/> '.
      'This Plugin uses the Event Interface which is triggered '.
      'wenn an event is saved in the underlying active event calendar'
      );

    $field = $section->add_textfield('kvm_interface_fairdb_url', 
                                     'URL');
    $field->set_description('URL to the OpenFairDB database. ' .
                            'The Karte von Morgen is build on top of this database. ' . 
                            'Default it is setted to the development database: ' . 
                            'https://dev.ofdb.io/v0. ' . 
                            'The production database is: https://api.ofdb.io/v0/');
    $field->set_defaultvalue('https://dev.ofdb.io/v0');

    $field = $section->add_textfield('kvm_access_token', 
                                     'Access token');
    $field->set_description('This token is you become from the Karte von Morgen ' . 
                            'after you have registered you organisation there. ' .
                            'With this token it is possible to upload and download ' .
                            'events and organisations.'); 

    $field = $section->add_textfield(
                         $module->get_kvm_fixed_tag_id(), 
                         'Fixed tag');
    $field->set_description('Gives uploaded events and entries a fixed tag '.
                            'so they all can be found by this tag '.
                            'This tag is also used as the special organisation hashtag ' .
                            'on which your organisation/platform will be registered ' . 
                            'by the Karte von Morgen.');

    if($this->is_module_enabled('wp-project'))
    {
      $field = $section->add_textfield(
        $module->get_kvm_fixed_project_tag_id(),
        'Fixed tag for projects');
      $field->set_description('This tag will be added to the tags transported'
                          . ' to the Karte von Morgen. Depend on the Tag ' 
                          . ' in the Karte von Morgen it is possible to '
                          . ' change the color of the pin on the map.');
      $field->set_defaultvalue($module->get_kvm_fixed_tag() . '-project');
    }

    if(!$rootmodule->are_developer_options_enabled())
    {
      $page->register();
      return;
    }

    $field = new class('kvm_event_defaultresult', 
                       'Loaded Events from KVM') 
                       extends UISettingsTextAreaField
    {
      public function get_value()
      {
        $mc = WPModuleConfiguration::get_instance();
        $instance = $mc->get_module('wp-kvm-interface');
        $result = '';
        try
        {
          $events = $instance->get_events();

          foreach ( $events as $event ) 
          {
            $result .= $event->to_text();
            $result .= PHP_EOL;
            $result .= '-----------------';
            $result .= PHP_EOL;
          }
        }
        catch(OpenFairDBApiException $e)
        {
          $result .= 'ERROR';
          $result .= PHP_EOL;
          $result .= 'Code: '. $e->getCode();
          $result .= PHP_EOL;
          $result .= 'Message: '. $e->getMessage();
          $result .= PHP_EOL;
          $result .= 'Trace: '. $e->getTraceAsString();
        }
        return $result;
      }
    };

    $field->set_register(false);
    $section->add_field($field);
    
    $field = new class('kvm_entry_defaultresult', 
                       'Loaded Entries from KVM') 
                       extends UISettingsTextAreaField
    {
      public function get_value()
      {
        $mc = WPModuleConfiguration::get_instance();
        $instance = $mc->get_module('wp-kvm-interface');

        $result = '';
        try
        {
          $organisations = $instance->get_entries();
    
          foreach ( $organisations as $organisation ) 
          {
            $result .= $organisation->to_text();
            $result .= PHP_EOL;
            $result .= '-----------------';
            $result .= PHP_EOL;
          }
        }
        catch(OpenFairDBApiException $e)
        {
          $result .= 'ERROR';
          $result .= PHP_EOL;
          $result .= 'Code: '. $e->getCode();
          $result .= PHP_EOL;
          $result .= 'Message: '. $e->getMessage();
          $result .= PHP_EOL;
          $result .= 'Trace: '. $e->getTraceAsString();
        }
        return $result;
      }
    };

    $field->set_register(false);
    $section->add_field($field);

    $page->register();
  }
}
