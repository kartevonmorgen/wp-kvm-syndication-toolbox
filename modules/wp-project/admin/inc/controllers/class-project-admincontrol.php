<?php

/**
 * Controller ProjectAdminControl
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class ProjectAdminControl extends UIAbstractAdminControl 
                          implements WPModuleStarterIF
{
  public function start() 
  {
    $rootmodule = $this->get_root_module();

    $page = new UISettingsPage('project-options', 
                               'Project settings');
    $page->set_submenu_page(true, $rootmodule->get_id() . '-menu');
    $section = $page->add_section('wplib_section_one', 'Project settings');
    $section->set_description(
      'If the Karte von Morgen Module ist activated, a special Hashtag ' .
      'can be setted for projects in the Karte von Morgen Module');

    $module = $this->get_current_module();
    if(empty($module))
    {
      return;
    }


    $field = $section->add_checkbox(
       $module->get_extend_the_content_for_single_project_id(), 
       'Extend the content of the project detail view.');
    $field->set_description('Extend the content of the ' .
                            'project detail view with. ' . 
                            'categories, tags, events, ' .
                            'karte von morgen and contact data');
    $field->set_defaultvalue(true);

    $page->register();
  }
}
