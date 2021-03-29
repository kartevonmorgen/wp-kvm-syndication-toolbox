<?php

class UserRegisterEmail
{
  public function setup($loader)
  {
    $loader->add_filter( 'wp_new_user_notification_email', 
                         $this, 'new_user_notification_email', 10, 3 );
  }

  function new_user_notification_email($new_user_notification_email, 
                                       $user, 
                                       $blogname )
  {
    $key = get_password_reset_key( $user );
    $user_login = stripslashes( $user->user_login );
    $user_email = stripslashes( $user->user_email );
    $login_url  = wp_login_url();
    $link = network_site_url( 'wp-login.php?action=rp&key=' . $key . 
                              '&login=' . rawurlencode( $user->user_login));

    $message = get_option('userregister_standardemail', $this->get_default_email());
    $message = str_replace('%USER_LOGIN%', $user_login, $message);
    $message = str_replace('%USER_EMAIL%', $user_email, $message);
    $message = str_replace('%CONFIRM_LINK%', $link, $message);

    $new_user_notification_email['headers'] = array('Content-Type: text/html; charset=UTF-8');
    $new_user_notification_email['message'] = $message;
    return $new_user_notification_email;
  }

  public function get_default_email()
  {
    $message = '<p>Hallo %USER_LOGIN%</p>';
    $message .= PHP_EOL;
    $message .= '<p>Wilkommen auf der %BLOGNAME% Plattform</p>';
    $message .= PHP_EOL;
    $message .= '<p>Benutzername: %USER_LOGIN%</p>';
    $message .= PHP_EOL;
    $message .= '<p>Email: %USER_EMAIL%</p>';
    $message .= PHP_EOL;
    $message .= '<p>Die Registrierung wird manuell bestätigt durch die Redaktion. Sobald die Registrierung freigegeben ist werden Sie eine Rückmeldung bekommen</p>    ';
    $message .= PHP_EOL;
    $message .= '<p>Bitte bestätigen Sie die Registrierung jetzt schon über den folgenden Link und stellen Sie ein Passwort ein: ';
    $message .= '<a href="%CONFIRM_LINK%">%CONFIRM_LINK%</a></p>';
    $message .= PHP_EOL;
    $message .= '<p>Wenn Sie Probleme haben, kontaktieren Sie Bitte die folgende Email-Adress    e: %ADMIN_EMAIL%:w</p>';
    $message .= PHP_EOL;
    $message .= '<p>Viel Erfolg auf dem Plattform!</p>';
    $message .= PHP_EOL;
    return $message;
  }
}
