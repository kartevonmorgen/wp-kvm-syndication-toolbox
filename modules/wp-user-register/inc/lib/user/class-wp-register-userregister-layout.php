<?php

class WPRegisterUserRegisterLayout
  extends WPAbstractModuleProvider
{

  public function setup($loader)
  {
    $loader->add_action('login_enqueue_scripts', 
                        $this, 
                        'login_logo');
  }

  function login_logo()
  {
    ?><style type="text/css"> 
body.login div#login h1 a 
{
  background-image: url(<?php echo get_option('userregister_logo', $this->get_default_logo()); ?>);   
  background-size: 310px;
  width: 510px;
  height: 130px;
  padding-bottom: 30px; 
} 
div#login
{
  width: 510px;
}
input#user_email
{
  background-color:#E3E892; 
}
</style><?php 
  }

  public function get_default_logo()
  {
    $root = $this->get_root_module();
    return 'wp-content/plugins/' . $root->get_id() . '/images/defaultlogo.png';
  }
}
