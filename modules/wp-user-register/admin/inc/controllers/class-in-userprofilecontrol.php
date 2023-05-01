<?php
/**
  * Controller InUserProfileControl
  *
  * @author  	Sjoerd Takken
  * @copyright 	No Copyright.
  * @license   	GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
  */
class InUserProfileControl extends UIControl
{
  public function init() 
  {
    $model = new InUserModel();
    $model->init();
    $this->set_model($model);

    $view = new InUserProfileView($this);
    $view->init();
    $this->set_view($view);

    // Benutzer Bearbeiten
    add_action( 'show_user_profile', 
      array($this, 'start_profile') );
    add_action( 'edit_user_profile', 
      array($this, 'start_profile') );

    add_action( 'personal_options_update', 
      array($this, 'save_profile' ));
    add_action( 'edit_user_profile_update', 
      array($this, 'save_profile' ));
  }

  public function start_profile( $user )
  {
    $this->set_property(UIModel::USER_ID, $user->ID );

    $module = $this->get_current_module();
    $post = $module->get_organisation_by_user($user->ID);
    if(!empty($post))
    {
      $this->set_property(UIModel::POST_ID, $post->ID );
    }

    $this->load();

    $this->get_view()->show();
  }

  public function save_profile($user_id)
  {
    if ( !current_user_can( 'edit_user', $user_id ) )
    {
      return false;
    }

    $this->set_property(UIModel::USER_ID, $user_id );
    $module = $this->get_current_module();
    $post = $module->get_organisation_by_user($user->ID);
    if(!empty($post))
    {
      $this->set_property(UIModel::POST_ID, $post->ID );
    }

    $this->load();
    $this->save();
  }
}
