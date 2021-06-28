<?php

/**
 * Controller UserRegisterAdminControl
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class UserRegisterAdminControl 
 extends UIAbstractAdminControl
 implements WPModuleStarterIF
{
  public function start() 
  {
    $rootmodule = $this->get_root_module();
    $module = $this->get_current_module();

    $page = new UISettingsPage('userregister-options', 
                               'User register settings');
    $page->set_submenu_page(true, $rootmodule->get_id() . '-menu');
    $section = $page->add_section('wplib_section_one', 'Userregister settings');
    $section->set_description('');

    $field = $section->add_checkbox($module->get_publish_organisation_after_approve_id(), 
                                   'Publish an Organisation direct after the approving');
    $field->set_description('Normally an Organisation will be published by the user itself ' . 
                            'but it is also possible to publish the Organisation directly ' . 
                            'after the registration, then the description of the organisation ' .
                            'should also be added to the registration form.');

    $field = $section->add_checkbox('userregister_createdefaultitems', 
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
    $layout = new WPRegisterUserRegisterLayout($module);
    $field->set_defaultvalue($layout->get_default_logo());

    $page->register();
  }
}
