<?php

/**
 * Controller OrganisationAdminControl
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class OrganisationAdminControl extends UIAbstractAdminControl 
                               implements WPModuleStarterIF
{
  public function start() 
  {
    $mc = WPModuleConfiguration::get_instance();
    $rootmodule = $mc->get_root_module();

    $page = new UISettingsPage('organisation-options', 
                               'Organisation settings');
    $page->set_submenu_page(true, $rootmodule->get_id() . '-menu');
    $section = $page->add_section('wplib_section_one', 'Organisation settings');
    $section->set_description(
      '');

    $module = $this->get_current_module();
    if(empty($module))
    {
      return;
    }

    $field = $section->add_checkbox($module->get_extend_the_content_for_single_organisation_id(), 
                                    'Extend the content of the organisation detail view.');
    $field->set_description('Extend the content of the organisation detail view with. ' . 
                            'categories, tags, events, karte von morgen and contact data');
    $field->set_defaultvalue(true);

    $field = $section->add_checkbox($module->get_multiple_organisation_pro_user_id(), 
                                    'Allow multiple Organisations pro User');
    $field->set_description('Normally a user registers himself and its organisation. ' . 
                            'In some cases you want to make it possible that  ' . 
                            'a User can create multiple Organisation.' . 
                            '(If this is option is enabled, ' . 
                            'for now it is not possible to create' .
                            'a organisation page with an eventslist ' .
                            'for the organisation)');
    $field->set_defaultvalue(false);

    $field = $section->add_checkbox($module->get_migration_enabled_id(), 
                                    'Migrate User und Initiative to a Organisation');
    $field->set_description('If you have already created initiative in the past ' .
                            'after the upgrade they still exists but are saved ' .
                            'under the old post type "initiative". ' . 
                            'If you enable this option, then the initiative ' .
                            'are visible and we can migrate them to ' . 
                            'organisations '); 
    $field->set_defaultvalue(false);

    $page->register();
  }
}
