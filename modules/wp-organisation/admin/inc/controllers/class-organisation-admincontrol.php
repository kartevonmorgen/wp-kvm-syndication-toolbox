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
    $rootmodule = $this->get_root_module();

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

    $page->register();
  }
}
