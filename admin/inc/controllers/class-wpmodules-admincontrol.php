<?php

/**
 * Controller PSR7AdminControl
 * Settings page of the PSR7 Wrapper around WP_Http.
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class WPModulesAdminControl implements WPModuleStarterIF
{
  public function __construct() 
  {
  }

  public function start() 
  {
    $mc = WPModuleConfiguration::get_instance();
    $rootmodule = $mc->get_root_module();
    $modules = $mc->get_modules();

    $page = new UISettingsPage($rootmodule->get_id(), 'Module Ein- und Ausschalten');
    $page->set_menutitle($rootmodule->get_name());
    $page->set_submenutitle('Modules');

    foreach($modules as $module)
    {
      $section = $page->add_section($module->get_module_enabled_id() . '-section', 
                                    $module->get_name());

      if(!empty($module->get_description()))
      {
        $section->add_description($module->get_description());
      }

      $parent_module = $module->get_parent_module();
      if(!empty($parent_module))
      {
        $section->add_description('Abhängig vom Modul: <b>' . 
                                  $parent_module->get_name() . '</b>');
      }
      
      $field = new UIModuleSettingsCheckBoxField($module, 'Eingeschaltet');
      $field = $section->add_field($field);
      $field->set_defaultvalue(false);
    }

    $page->register();
  }
}
