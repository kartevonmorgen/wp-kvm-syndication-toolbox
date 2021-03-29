<?php

/**
 * Controller UserRegisterAdminControl
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class UserRegisterAdminControl implements WPModuleStarterIF
{
  public function __construct() 
  {
  }

  public function start() 
  {
    $mc = WPModuleConfiguration::get_instance();
    $rootmodule = $mc->get_root_module();

    $page = new UISettingsPage('userregister-options', 
                               'User register settings');
    $page->set_submenu_page(true, $rootmodule->get_id() . '-menu');
    $section = $page->add_section('wplib_section_one', 'Userregister settings');
    $section->set_description(
      '');

    $field =$section->add_checkbox('userregister_createdefaultitems', 
                                   'Create default userregister items');
    $field->set_description('New items are created wenn the checkbox was saved with off and ' . 
                            'is switched from off to on and saved again ' . 
                            'New items are only created if there are no other items.');

    $field = $section->add_textarea('userregister_standardemail', 
                            'Email that will be send after register');
    $field->set_description('This Email will be send after a user has registered ' . 
                            'on the website');
    $uremail = new UserRegisterEmail();
    $field->set_defaultvalue($uremail->get_default_email());

    $field = $section->add_textfield('userregister_logo',
                                     'Path to Logo for the Loginpage');
    $layout = new WPRegisterUserRegisterLayout();
    $field->set_defaultvalue($layout->get_default_logo());

    $page->register();
  }
}
