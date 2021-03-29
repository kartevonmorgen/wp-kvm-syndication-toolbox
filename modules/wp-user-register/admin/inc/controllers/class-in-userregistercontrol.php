<?php
/**
  * Controller InRegisterControl
  *
  * @author  	Sjoerd Takken
  * @copyright 	No Copyright.
  * @license   	GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
  */
class InUserRegisterControl extends UIControl
{
  public function init() 
  {
    $model = new InUserModel();
    $model->init();
    $this->set_model($model);

    $view = new InUserRegisterView($this);
    $view->init();
    $this->set_view($view);


    // Registrierung
    add_action( 'register_form', 
      array($this, 'start_register') );

    add_filter( 'registration_errors',
                array($this,'validate_register'), 10, 3 );
    add_action( 'user_register', 
                array($this,'save_register'));

  }

  public function start_register()
  {
    $this->load();
    $this->get_view()->show();
  }

  public function validate_register( $errors,
                            $user_login, 
                            $user_email )
  {
    return $this->validate( $errors );
  }

  public function save_register($user_id)
  {
    $this->load();
    $this->set_property(UIModel::USER_ID, $user_id);

    $organisation_post_id = $this->create_organisation($user_id);
    $this->set_property(UIModel::POST_ID, $organisation_post_id);
    $this->save();

    // Doing extra updates
    $fname = $this->get_value('first_name'); 
    $lname = $this->get_value('last_name');

    $args = array(
      'ID' => $user_id,
      'display_name' => $fname . ' ' . $lname);
    wp_update_user( $args );
  }

  private function create_organisation($user_id)
  {
    $post_title = $this->get_value('post_title');
    $post_name = sanitize_title( $post_title );
    $imetainput = array(
      'organisation_firstname' => $this->get_value('first_name'),
      'organisation_lastname' => $this->get_value('last_name'),
      'organisation_phone' => $this->get_value('dbem_phone'),
      'organisation_email' => $this->get_value('user_email'),
      'organisation_website' => $this->get_value('user_url'));
      
    $ipost = array(
      'comment_status' => 'closed',
      'post_author' => $user_id,
      'post_category' => array(1),
      'post_content' => '<!-- wp:paragraph -->'.
                         '<p>Schreibe hier etwas über deine Organisation</p>'.
                         '<!-- /wp:paragraph -->',
      'post_title' => $post_title,
      'post_name' => $post_name,
      'post_status' => 'draft',
      'post_type' => 'organisation',
      'meta_input' => $imetainput,
      );
 
    // Insert the post into the database
    return wp_insert_post( $ipost, true );
  }

}
