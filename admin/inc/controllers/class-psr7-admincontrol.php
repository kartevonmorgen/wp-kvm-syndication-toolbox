<?php

/**
 * Controller PSR7AdminControl
 * Settings page of the PSR7 Wrapper around WP_Http.
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class PSR7AdminControl implements WPModuleStarterIF
{
  public function __construct() 
  {
  }

  public function start() 
  {
    $mc = WPModuleConfiguration::get_instance();
    $rootmodule = $mc->get_root_module();

    $page = new UISettingsPage('general-options', 
                               'General settings (Logging, Media, OSM)');
    $page->set_menutitle('General settings');
    $page->set_submenu_page(true, $rootmodule->get_id() . '-menu');
    $section = $page->add_section('wplib_section_one', 'Maximal media upload settings');
    $section->set_description(
      'Enable maximal media upload size and give the value in kBytes');

    $section->add_checkbox('wplib_max_media_uploadenabled', 
                           'Maximum media upload enabled');

    $field = $section->add_textfield('wplib_max_media_uploadsize', 
                            'Maximum upload for Media');
    $field->set_description('Maximum upload size for images, audio and video in kB');
    $field->set_defaultvalue('500');

    $section = $page->add_section('wplib_section_two', 'Developer and Administrator options');
    $section->set_description(
      'Developer and Administrator options for testing purposes');
    $field = $section->add_checkbox($rootmodule->get_developer_options_id(), 
                            'Enabled developer options');
    $field->set_defaultvalue(false);

    if($rootmodule->are_developer_options_enabled())
    {
      $field = $section->add_checkbox($rootmodule->get_reset_log_manual_id(), 
                                      'Reset the logging manual');
      $field->set_description('Do not automatically reset the logging, ' . 
                              'by default it is off');
      $field->set_defaultvalue(false);

      $field = $section->add_checkbox($rootmodule->get_manual_post_save_actions_id(), 
                                      'Add manual post save actions');
      $field->set_description('Add extra actions in the overview menu for posts, ' . 
                              'to see what happens for testing');
      $field->set_defaultvalue(false);

    }

   
    $section = $page->add_section('wplib_section_three', 'OSM Nominatim settings');
    $section->set_description(
      'OSM Nominatim search the coordinates for an address ');
    $field = $section->add_textfield('osm_nominatim_url', 
                            'OSM Nominatim URL');
    $field->set_defaultvalue(OsmNominatim::DEFAULT_URL);
    $field->set_description('This URL ist used to fill the coordinates of a location, by getting this information over the Open Street Map Nominatim API');

    $section = $page->add_section('wplib_section_four', 'Testing OSM Nominatim');
    $section->set_description(
      'Give an address and test this with OSM Nominatim ');
    
    $section->add_textfield('osm_nominatim_address', 
                            'Address');
    $section->add_textfield('osm_nominatim_zip', 
                            'Zipcode');
    $section->add_textfield('osm_nominatim_city', 
                            'City');
    $section->add_textfield('osm_nominatim_country', 
                            'Country');

    $field = new class('osm_nominatim_test_result', 
                       'Output from Test') extends UISettingsTextAreaField
    {
      public function get_value()
      {
        $uri = '';
        $uri .= get_option('osm_nominatim_url', OsmNominatim::DEFAULT_URL);
        $uri .= '/search?q=';
        $uri .= get_option('osm_nominatim_address', '');
        $uri .= ', ';
        $uri .= get_option('osm_nominatim_zip', '');
        $uri .= ', ';
        $uri .= get_option('osm_nominatim_city', '');
        $uri .= ', ';
        $uri .= get_option('osm_nominatim_country', '');
        $uri .= '&format=xml&addressdetails=1';
        if(empty($uri))
        {
          return 'No URI found';
        }

        $req = new SimpleRequest('get', $uri);
        $client = new WordpressHttpClient();
        $resp = $client->send($req);

        $result = 'TEST URI: ';
        $result .= $uri;
        $result .= '' . PHP_EOL;
        $result .= '' . PHP_EOL;
        $result .= $resp->getStatusCode();
        $result .= ' ';
        $result .= $resp->getReasonPhrase();
        $result .= '' . PHP_EOL;
        $result .= 'HEADERS: '. PHP_EOL;
        foreach($resp->getHeaders() as $key => $value)
        {
          $result .= '  [' . $key . ']: '.$value . PHP_EOL;
        }
        $result .= 'BODY: '. PHP_EOL;
        $result .= $resp->getBody();
        return $result;
      }
    };

    $field->set_register(false);
    $section->add_field($field);

    $section = $page->add_section('wplib_section_five', 'Logging');
    $section->set_description(
      'Outputs the Logging of the DefaultLogger ');
    $section->add_textarea('scw_toolbox_log', 
                            'Log Output');

    $page->register();
  }
}
