<?php

class InUserModel extends UIModel
{
  public function init()
  {
    $bgc = $this->add_bgcolor( 
      new UIColor('white', 'White', 'FFFFFF'));
    $bgc = $this->add_bgcolor( 
      new UIColor('yellow', 'Yellow', 'E3E892'));

    // Organisation Post Modeladapters
    $ma = $this->add_ma(
      new UIPostModelAdapter('post_title', UIModelAdapterType::TEXT));
    $ma->set_title('Name der Organisation');
    $ma->set_validate(true);


    $ma = $this->add_ma(
      new UIPostMetaModelAdapter(
                 'organisation_type', UIModelAdapterType::COMBOBOX));
    $ma->set_title('Organisationstype');
    $ma->set_description('Sind Sie ein Unternehmen oder Initiative ?');

    $mc = WPModuleConfiguration::get_instance();
    $module = $mc->get_module('wp-organisation');
    foreach($module->get_organisation_types() as $type)
    {
      $ma->add_choice($type->get_id(), $type->get_name());
    }
    $ma->set_default_value(WPOrganisationType::INITIATIVE);

    $ma = $this->add_ma(
      new UIPostMetaModelAdapter('organisation_address', UIModelAdapterType::TEXT));
    $ma->set_title('Strasse und Nr.');
    $ma->set_validate(true);

    $ma = $this->add_ma(
      new UIPostMetaModelAdapter('organisation_zipcode', UIModelAdapterType::TEXT));
    $ma->set_title('Postleitzahl');
    $ma->set_validate(true);

    $ma = $this->add_ma(
      new UIPostMetaModelAdapter('organisation_city', UIModelAdapterType::TEXT));
    $ma->set_title('Ort');
    $ma->set_validate(true);

    $ma = $this->add_ma(
      new UIPostMetaModelAdapter('organisation_lat', UIModelAdapterType::TEXT));
    $ma->set_title('Latitude');
    $ma->set_disabled(true);

    $ma = $this->add_ma(
      new UIPostMetaModelAdapter('organisation_lng', UIModelAdapterType::TEXT));
    $ma->set_title('Longitude');
    $ma->set_disabled(true);

    $ma = $this->add_ma(
      new UIPostMetaModelAdapter('organisation_kvm_id', UIModelAdapterType::TEXT));
    $ma->set_title('Karte von Morgen Id');
    if( !current_user_can('administrator')) 
    {
      $ma->set_disabled(true);
    }

    $ma = $this->add_ma(
      new UIPostMetaModelAdapter('organisation_kvm_upload', UIModelAdapterType::BOOLTYPE));
    $ma->set_title('Upload zu der Karte von Morgen');
    $ma->set_default_value(true);

    $ma = $this->add_ma(
      new UIPostMetaModelAdapter('organisation_kvm_log', UIModelAdapterType::TEXTAREA));
    $ma->set_title('Karte von Morgen Statusmeldungen');
    $ma->set_disabled(true);

    // User Modeladapters
    $ma = $this->add_ma(
      new UIUserMetaModelAdapter('organisation_ds', UIModelAdapterType::BOOLTYPE));
    $ma->set_title('Datenschutzerklärung akzeptiert');
    $ma->set_description('Sie sind mit der Datenschutzerklärung einverstanden');
    $ma->set_validate(true);

    $ma = $this->add_ma(
      new UIUserMetaModelAdapter('first_name', UIModelAdapterType::TEXT));
    $ma->set_title('Kontaktperson Vorname ');

    $ma = $this->add_ma(
      new UIUserMetaModelAdapter('last_name', UIModelAdapterType::TEXT));
    $ma->set_title('Kontaktperson Nachname ');
    $ma->set_validate(true);

    $ma = $this->add_ma(
      new UIUserMetaModelAdapter('dbem_phone', UIModelAdapterType::TEXT));
    $ma->set_title('Phone');

    $ma = $this->add_ma(
      new UIUserMetaModelAdapter('user_url', UIModelAdapterType::TEXT));
    $ma->set_title('Webseite');

    $ma = $this->add_ma(
      new UIUserMetaModelAdapter('user_email', UIModelAdapterType::TEXT));
    $ma->set_title('Email');

    $ma = $this->add_ma(
      new UserOldValuesModelAdapter('organisation_oldvalues', 
        UIModelAdapterType::TEXTAREA));
    $ma->set_title('Altes von früher');
    $ma->set_disabled(true);
  }

  protected function before_save_model()
  {
    if($this->is_address_changed())
    {
      $wpLocation = $this->create_wplocation();
      $wpLocHelper = new WPLocationHelper();
      $wpLocation = $wpLocHelper->fill_by_osm_nominatim(
                                    $wpLocation);
      $this->set_value('organisation_lng', $wpLocation->get_lon());
      $this->set_value('organisation_lat', $wpLocation->get_lat());
    }
  }

  protected function save_model()
  {
  }

  private function is_address_changed()
  {
    return 
      $this->is_value_changed('organisation_address') ||
      $this->is_value_changed('organisation_zipcode') ||
      $this->is_value_changed('organisation_city'); 
  }

  private function create_wplocation()
  {
    $wpLocHelper = new WPLocationHelper();

    $wpLocation = new WPLocation();
    $wpLocHelper->set_address($wpLocation, 
                              $this->get_value('organisation_address'));
    $wpLocation->set_zip($this->get_value('organisation_zipcode'));
    $wpLocation->set_city($this->get_value('organisation_city'));
    $wpLocation->set_name($this->get_value('organisation_name'));
    return $wpLocation;
  }
}
