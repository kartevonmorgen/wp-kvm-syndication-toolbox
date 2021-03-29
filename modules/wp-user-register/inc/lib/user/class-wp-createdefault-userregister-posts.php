<?php

class WPCreateDefaultUserRegisterPosts 
{
  public function setup($loader)
  {
    $loader->add_action('update_option', $this, 'execute',10,3);
  }

  public function execute($option, $old_value, $value)
  {
    if($option !== 'userregister_createdefaultitems')
    {
      return;
    }

    if($old_value)
    {
      return;
    }

    if(!$value)
    {
      return;
    }

    if($this->has_posts())
    {
      return;
    }
    $this->fill_default_posts();

  }
  private function has_posts()
  {
    $args = array(
       'post_type' => 'urpost');
    $urposts = get_posts($args);
    return (count($urposts) > 0);
  }

  private function fill_default_posts()
  {
    $field = new WPUserRegisterField();
    $field->set_id('contact-part');
    $field->set_type_id('text');
    $field->set_title('Kontaktdaten');
    $field->set_position(0);
    $field->insert_post();
    
    $field = new WPUserRegisterField();
    $field->set_id('post_title');
    $field->set_title('Name der Organisation');
    $field->set_position(1);
    $field->set_bgcolor_id('yellow');
    $field->insert_post();

    $field = new WPUserRegisterField();
    $field->set_id('organisation_type');
    $field->set_title('Type');
    $field->set_position(2);
    $field->set_bgcolor_id('yellow');
    $field->insert_post();

    $field = new WPUserRegisterField();
    $field->set_id('organisation_address');
    $field->set_title('Strasse und Nr.');
    $field->set_position(3);
    $field->set_bgcolor_id('yellow');
    $field->insert_post();

    $field = new WPUserRegisterField();
    $field->set_id('organisation_zipcode');
    $field->set_title('Postleitzahl');
    $field->set_position(4);
    $field->set_bgcolor_id('yellow');
    $field->insert_post();

    $field = new WPUserRegisterField();
    $field->set_id('organisation_city');
    $field->set_title('Ort');
    $field->set_position(5);
    $field->set_bgcolor_id('yellow');
    $field->insert_post();

    $field = new WPUserRegisterField();
    $field->set_id('first_name');
    $field->set_title('Vorname');
    $field->set_position(6);
    $field->insert_post();

    $field = new WPUserRegisterField();
    $field->set_id('last_name');
    $field->set_title('Nachname');
    $field->set_position(7);
    $field->insert_post();

    $field = new WPUserRegisterField();
    $field->set_id('dbem_phone');
    $field->set_title('Telephone');
    $field->set_position(8);
    $field->set_bgcolor_id('yellow');
    $field->insert_post();

    $field = new WPUserRegisterField();
    $field->set_id('user_url');
    $field->set_title('Webseite');
    $field->set_position(9);
    $field->set_bgcolor_id('yellow');
    $field->insert_post();

    $field = new WPUserRegisterField();
    $field->set_id('organisation_ds');
    $field->set_title('Datenschutzerklärung akzeptiert');
    $field->set_position(10);
    $field->insert_post();

    $field = new WPUserRegisterField();
    $field->set_id('privacy-policy-part');
    $field->set_type_id('text');
    $field->set_title('Datenschutz');
    $field->set_description('<p>Durch die Registrierung auf dieser Plattform werden die in Gelb eingegebenen Daten veröffentlicht.<br/>Die weißen Felder werden nur für interne Zwecke verwendet.<br/>Wenn Sie sich Anmelden können Sie eine Beschreibung von Ihrer Organisation und Ihren Veranstaltungen eingeben. Diese Eingaben werden automatisch auf dieser Webseite und auf der Webseite <a href="https://www.kartevonmorgen.org">www.kartevonmorgen.org</a> veröffentlicht.<br/>' .
'Mehr Informationen über die Verarbeitung von personenbezogenen Daten sind in unserer <a href="' . network_site_url('/ds/') .'">Datenschutzerklärung</a> zu lesen</p>');
    $field->set_position(11);
    $field->insert_post();

    $field = new WPUserRegisterField();
    $field->set_id('manual-part');
    $field->set_type_id('text');
    $field->set_title('Anleitung');
    $field->set_position(12);
    $field->set_description('<p><em>Eine <a href="' . network_site_url('/manual/') . '" data-type="page" data-id="351">ausgebreitete Anleitung</a> wie man sich registrieren </em><br><em>und wie man seine Organisation und Veranstaltungen </em><br><em>eintragen kann, findet man <a href="' . network_site_url('/manual/') . '" data-type="page" data-id="351">hier</a>.</em></p>');
    $field->insert_post();

  }

}
