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

    $module = $this->get_parent_module();
    $userProfile = new InUserProfileControl($this->get_current_module());
    $userProfile->init();
  }
}
