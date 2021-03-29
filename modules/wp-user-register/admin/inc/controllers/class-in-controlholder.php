<?php

class InControlHolder implements WPModuleStarterIF
{
  private $userRegister;
  private $userProfile;
 
  public function start()
  {
    $userRegister = new InUserRegisterControl();
    $userRegister->init();

    $mc = WPModuleConfiguration::get_instance();
    $module = $mc->get_module('wp-organisation');
    if(!$module->is_multiple_organisation_pro_user_allowed())
    {
      $userProfile = new InUserProfileControl();
      $userProfile->init();
    }
  }
}
