<?php

class InControlHolder extends WPAbstractModuleProvider
                      implements WPModuleStarterIF
{
  private $userRegister;
  private $userProfile;
 
  public function start()
  {
    $userRegister = new InUserRegisterControl($this->get_current_module());
    $userRegister->init();

    $mc = WPModuleConfiguration::get_instance();
    $module = $mc->get_module('wp-organisation');
    if(!$module->is_multiple_organisation_pro_user_allowed())
    {
      $userProfile = new InUserProfileControl($this->get_current_module());
      $userProfile->init();
    }
  }
}
