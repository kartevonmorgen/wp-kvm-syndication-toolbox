<?php

/**
 * Controller Newsletter Interface AdminControl
 * Settings page of the Newsletter Interface.
 * It uses the UISettingsPage which use the 
 * Wordpress Settings API
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class WPNewsletterInterfaceAdminControl 
                     extends UIAbstractAdminControl
                     implements WPModuleStarterIF
{
  public function start() 
  {
    $rootmodule = $this->get_root_module();

    $page = new UISettingsPage('newsletter-interface-options', 
                               'Newsletter interface');
    $page->set_submenu_page(true, $rootmodule->get_id() .'-menu');
    
    $section = $page->add_section('ni_section_one', 'Settings');
    $section->set_description(
      'Here we are able to set the newsletter plugin and defaults for '.
      'exporting events to the newsletter plugin setted. <br/> '.
      'Plugins that are using this interface are indepent of the '.
      'underlying active newsletter plugin, so they do not have to '.
      'write code for the many different newsletter plugins '.
      'that exists in Wordpress');

    $module = $this->get_current_module();
    $field = $section->add_dropdownfield($module->get_newsletter_adapter_id(), 
                                         'Newsletter plugin');
	  $available_adapters = $module->get_available_newsletter_adapters();
    foreach($available_adapters as $adapter)
    {
      $field->add_value( $adapter->get_id(), 
                         $adapter->get_description());
    }
    $field = $section->add_textfield(
      $module->get_number_of_days_id(), 
      'Number of days in the event list');
    $field->set_description('How long in future will events be shown in the Newsletter');
    $field->set_defaultvalue(
      $module->get_default_number_of_days());

    $field = $section->add_textfield(
      $module->get_selected_category_id(), 
      'Select the category to be shown');
    $field->set_description('Select one or more event categories that will be shown in the newsletter, if empty, all events will be shown');

    $section->add_checkbox(
      $module->get_newsletter_lists_support_enabled_id(), 
      'Are Newsletter lists supported');


    $page->register();
  }
}
