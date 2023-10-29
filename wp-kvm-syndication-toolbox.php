<?php
/*
Plugin Name: WP KVM Syndication Toolbox
Plugin URI: https://github.com/kartevonmorgen/wp-kvm-syndication-toolbox
Description: Tools for Transition
Version: 1.0
Author: Sjoerd Takken
Author URI: https://www.sjoerdscomputerwelten.de/
License: GPL2

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

defined('ABSPATH') or die('No script kiddies please!');

// Early includes, needed for usage of modules.
include_once( dirname( __FILE__ ) . '/inc/lib/module/class-wp-moduleconfiguration.php');
include_once( dirname( __FILE__ ) . '/inc/lib/module/class-wp-moduleloader.php');
include_once( dirname( __FILE__ ) . '/inc/lib/module/class-wp-abstractmodule.php');
include_once( dirname( __FILE__ ) . '/inc/lib/module/class-wp-abstractplugin.php');
include_once( dirname( __FILE__ ) . '/modules/wp-events-interface/class-wp-events-interface.php');
include_once( dirname( __FILE__ ) . '/modules/wp-events-feed-importer/class-wp-events-feed-importer.php');
include_once( dirname( __FILE__ ) . '/modules/wp-kvm-interface/class-wp-kvm-interface.php');
include_once( dirname( __FILE__ ) . '/modules/wp-organisation/class-wp-organisation.php');
include_once( dirname( __FILE__ ) . '/modules/wp-simple-events/class-wp-simple-events.php');
include_once( dirname( __FILE__ ) . '/modules/wp-project/class-wp-project.php');
include_once( dirname( __FILE__ ) . '/modules/wp-dashboard-posts/class-wp-dashboard-posts.php');
include_once( dirname( __FILE__ ) . '/modules/wp-user-register/class-wp-user-register.php');
include_once( dirname( __FILE__ ) . '/modules/wp-ess-event-calendar-client/class-wp-ess-event-calendar-client.php');
include_once( dirname( __FILE__ ) . '/modules/wp-newsletter-interface/class-wp-newsletter-interface.php');
include_once( dirname( __FILE__ ) . '/modules/wp-commonsbooking-extensions/class-wp-commonsbooking-extensions.php');

class WPKVMSyndicationToolboxPlugin extends WPAbstractPlugin
{
  private $_wplocation_freetextformattypes = array();

  public function setup_modules()
  {
    $ei_module = $this->add_module(new WPEventsInterfaceModule('Events interface'));
    $ei_module->set_description('Das Events interface sorgt dafür das ' .
                             'weitere Module eine eindeutige Schnittstelle ' .
                             'haben um Veranstaltungen zu speichern ' .
                             'oder zu lesen ohne zu wissen welche ' .
                             'Wordpress Plugin für ' .
                             'Veranstaltungen benutzt wird. Im Moment werden die folgende ' .
                             'Veranstaltung Plugins unterstutzt: ' .
                             'Events Manager (lesen und schreiben), Events Calendar (lesen), ' .
                             'All in One Events Calendar (lesen)');
    
    $ss_module = $ei_module->add_module(new WPEventsFeedImporterModule('Events feed importer'));
    $ss_module->set_description('Der Events feed importer sorgt dafür das Veranstaltungen ' .
                             'über Feeds importiert werden können. ' .
                             'Das Events Interface Module wird benutzt um die Veranstaltungen ' .
                             'auf dem aktivierte Veranstaltungsplugin abzuspeichern. ' .
                             'Das Events Feed Importer Modul unterstützt ESS Feeds und das iCal Feeds. ');
    $se_module = $this->add_module(new WPSimpleEventsModule('Simple events'));
    $se_module->set_description('Dieses Module erstellt eine einfache  ' .
                               'Veranstaltungskalendar, dadurch kann man  ' .
                               'versichten auf eine Event Calendar Plugin ' . 
                               'und diese benutzen im Events Interface ' .
                               'und Feed Importer');
    
    $kvm_module = $ei_module->add_module(new WPKVMInterfaceModule('Karte von morgen'));
    $kvm_module->set_description('Der Karte von morgen Schnittstelle sorgt dafür das Veranstaltungen und ' .
                                'Organisation auf die Karte von morgen hochgeladen werden ' .
                                'Veranstaltungen werden über Das Events interface geladen und weiter ' .
                                'gegeben an der Karte von morgen. ' .
                                'Die Initiativen werden aus dem Initiative-Modul geladen und weiter gegeben ' .
                                'an der Karte von morgen. ');
    
    $i_module = $this->add_module(new WPOrganisationModule('Organisation'));
    $i_module->set_description('Das Organisation Modul ermöglich die Eingabe von Organisation ' .
                               'inklusiv Eingabe von Kontaktdaten und Kontaktperson ' .
                               'Wenn das Karte von morgen Modul aktiviert ist ' . 
                               'wird die Organisation ' .
                               'hochgeladen zu der Karte von morgen ');

    $p_module = $i_module->add_module(new WPProjectModule('Projekte'));
    $p_module->set_description('Das Projekte Modul ermöglich die Eingabe ' .
                               'von Projekte für ein Organisation ' .
                               'inklusiv Eingabe von Kontaktdaten und Kontaktperson ' .
                               'Wenn das Karte von morgen Modul aktiviert ist ' . 
                               'wird das Projekt als Initiative ' .
                               'hochgeladen zu der Karte von morgen ');
    
    $user_module = $i_module->add_module(new WPUserRegisterModule('Benutzer registrieren'));
    $user_module->set_description('Das Modul sorgt dafür das Benutzer sich gleich mit ihre Organisation ' . 
                               'registrieren können. ');

    $db_module = $this->add_module(new WPDashboardPostsModule('Dashboard Beiträge'));
    $db_module->set_description('Das Modul ermöglicht es, Beitrage ' .
                                'für das Dashboard zu erstellen ' .
                                'und entfernt die Standard Wordpress Ansicht');
    $ess_client_module = $ei_module->add_module(new WPESSEventCalendarClientModule('ESS Event Calendar client'));
    $ess_client_module ->set_description('Das Modul kann auf einem Client ' . 
                                         'benutzt werden um Veranstaltungen ' .
                                         'als ESS-Feed zur Verführung ' . 
                                         'zu stellen. Die können dann auf ' .
                                         'eine andere Webseite mit dem ' .
                                         'Events feed importer ' .
                                         'importiert werden über ESS');

    $ni_module = $this->add_module(new WPNewsletterInterfaceModule('Newsletter Interface'));
    $ni_module ->set_description('Das Modul kann events in ein Newsletter importieren ' . 
                                 'und unterstützt mehrere Newsletter Plugins in Wordpress ');

    $cbe_module = $this->add_module(new WPCommonsBookingExtensionsModule('Commons Booking Extensions'));
    $cbe_module ->set_description('Das Modul ermöglicht Zeitrahmen auf alle Artikel ' . 
                                 'zu duplizieren fürs Commons Booking Plugin ' .
                                 'Man erstellt ein Zeitrahmen ohne ein Artikel zu wahlen ' .
                                 'in Entwurf Modus. Dann wahlt man die Aktion '. 
                                 '"Duplizier Zeitrahmen für alle Artikel" ' . 
                                 'Dann werden erst alle Zeitrahmen für diese Standort '.
                                 'entfernt und gleich danach wieder erstellt aus dem ' .
                                 'Zeitrahmen ohne Artikel in Entwurf Modus');
  }

  public function setup_includes($loader)
  {
    // PHPUtil
    $loader->add_include('/inc/lib/util/class-phpstringutil.php');
    $loader->add_include('/inc/lib/util/class-phparrayutil.php');

    // ICalendar
    $loader->add_include('/inc/lib/icalendar/class-icallogger.php');
    $loader->add_include('/inc/lib/icalendar/class-icaldatehelper.php');
    $loader->add_include('/inc/lib/icalendar/class-icalvline.php');
    $loader->add_include('/inc/lib/icalendar/class-icalveventdate.php');
    $loader->add_include('/inc/lib/icalendar/class-icalveventorganizer.php');
    $loader->add_include('/inc/lib/icalendar/class-icalveventgeo.php');
    $loader->add_include('/inc/lib/icalendar/class-icalveventcategories.php');
    $loader->add_include('/inc/lib/icalendar/class-icalveventtext.php');
    $loader->add_include('/inc/lib/icalendar/class-icalveventlocation.php');
    $loader->add_include('/inc/lib/icalendar/class-icalveventrecurringdate.php');
    $loader->add_include('/inc/lib/icalendar/class-icalvevent.php');
    $loader->add_include('/inc/lib/icalendar/class-icalvcalendar.php');

    // Add txt to Image
    $loader->add_include('/inc/lib/img/tti-text-util.php');
    $loader->add_include('/inc/lib/img/max_media_upload.php');
    $loader->add_include('/inc/lib/log/class-logresult.php');
    $loader->add_include('/inc/lib/log/class-abstractlogger.php');
    $loader->add_include('/inc/lib/log/class-postmetalogger.php');
    $loader->add_include('/inc/lib/log/class-usermetalogger.php');

    // -- Http Wrapper --
    $loader->add_include('/inc/lib/http/class-message-interface.php');
    $loader->add_include('/inc/lib/http/class-response-interface.php');
    $loader->add_include('/inc/lib/http/class-request-interface.php');
    $loader->add_include('/inc/lib/http/class-simple-message.php');
    $loader->add_include('/inc/lib/http/class-simple-response.php');
    $loader->add_include('/inc/lib/http/class-simple-request.php');
    $loader->add_include('/inc/lib/http/class-client-interface.php');
    $loader->add_include('/inc/lib/http/class-wordpress-http-client.php');

    // -- WP Helper Classes --
    $loader->add_include('/inc/lib/wp/class-wpentry.php' );
    $loader->add_include('/inc/lib/wp/class-wpentry-type-type.php');
    $loader->add_include('/inc/lib/wp/class-wporganisation.php' );
    $loader->add_include('/inc/lib/wp/class-wpproject.php' );
    $loader->add_include('/inc/lib/wp/class-wplocation-freetextformat-type.php' );
    $loader->add_include('/inc/lib/wp/class-wplocation.php' );
    $loader->add_include('/inc/lib/wp/class-wplocationhelper.php' );
    $loader->add_include('/inc/lib/wp/class-wpcategory.php' );
    $loader->add_include('/inc/lib/wp/class-wptag.php' );
    $loader->add_include('/inc/lib/wp/class-wplink.php' );
    $loader->add_include('/inc/lib/wp/class-wpmetafieldshelper.php' );

    // -- OpenStreetMap Nominatim --
    $loader->add_include('/inc/lib/osm/class-osm-nominatim.php' );
    $loader->add_include('/inc/lib/osm/class-osm-nominatim-cache.php' );

    // -- Opening Hours --
    $loader->add_include('/inc/lib/openinghours/class-openinghours.php' );
    $loader->add_include('/inc/lib/openinghours/class-openinghours-day.php' );
    $loader->add_include('/inc/lib/openinghours/class-openinghours-day-type.php' );
    $loader->add_include('/inc/lib/openinghours/class-openinghours-day-types.php' );
    $loader->add_include('/inc/lib/openinghours/class-openinghours-timerange.php' );
    $loader->add_include('/inc/lib/openinghours/class-openinghours-timerange-set.php' );

    // -- Module Provider --
    $loader->add_include('/inc/lib/module/class-wp-abstractmoduleprovider.php');
    
    // -- UI Tools Metabox --
    $loader->add_include('/inc/lib/ui/class-ui-metabox-field.php' );
    $loader->add_include('/inc/lib/ui/class-ui-metabox-openinghours-field.php' );
    $loader->add_include('/inc/lib/ui/class-ui-metabox.php' );


    // -- UI Tools Settings --
    $loader->add_include('/inc/lib/ui/class-ui-page.php' );
    $loader->add_include('/inc/lib/ui/class-ui-settings-field.php' );
    $loader->add_include('/inc/lib/ui/class-ui-settings-section.php' );
    $loader->add_include('/inc/lib/ui/class-ui-settings-page.php' );

    // UI Table Actions
    $loader->add_include('/inc/lib/ui/class-ui-dropdown-posts.php' );
    $loader->add_include('/inc/lib/ui/class-ui-tableaction.php' );
    $loader->add_include('/inc/lib/ui/class-ui-posttableaction.php' );
    $loader->add_include('/inc/lib/ui/class-ui-posttableaction-if.php' );
    $loader->add_include('/inc/lib/ui/class-ui-usertableaction.php' );
    $loader->add_include('/inc/lib/ui/class-ui-usertableaction-if.php' );

    // UI Support for AdminControl
    $loader->add_include('/inc/lib/ui/class-ui-abstractadmincontrol.php' );

    // -- UI MVC Tools
    $loader->add_include('/inc/lib/ui/models/class-ui-color.php');
    $loader->add_include('/inc/lib/ui/models/class-ui-choice.php');
    $loader->add_include('/inc/lib/ui/models/class-ui-modeladapter-type.php');
    $loader->add_include('/inc/lib/ui/models/class-ui-modeladapter.php');
    $loader->add_include('/inc/lib/ui/models/class-ui-post_modeladapter.php');
    $loader->add_include('/inc/lib/ui/models/class-ui-postmeta_modeladapter.php');
    $loader->add_include('/inc/lib/ui/models/class-ui-usermeta_modeladapter.php');
    $loader->add_include('/inc/lib/ui/models/class-ui-model.php');
    $loader->add_include('/inc/lib/ui/views/class-ui-viewadapter.php');
    $loader->add_include('/inc/lib/ui/views/class-ui-va-textfield.php');
    $loader->add_include('/inc/lib/ui/views/class-ui-va-textarea.php');
    $loader->add_include('/inc/lib/ui/views/class-ui-va-checkbox.php');
    $loader->add_include('/inc/lib/ui/views/class-ui-va-combobox.php');
    $loader->add_include('/inc/lib/ui/views/class-ui-view.php');
    $loader->add_include('/inc/lib/ui/controllers/class-ui-control.php');

    // -- Controllers --
    $loader->add_include('/admin/inc/controllers/ui/class-ui-modulesettingscheckbox-field.php');
    $loader->add_include('/admin/inc/controllers/class-wpmodules-admincontrol.php' );
    $loader->add_include('/admin/inc/controllers/class-psr7-admincontrol.php' );

    // Helper class for creating templates for single-posts
    // and a list of posts with a setted post_type
    $loader->add_include('/inc/lib/template/class-wp-templatehelper.php');

    // Helper class for executing cronjobs
    $loader->add_include('/inc/lib/cron/class-abstractcronjob.php');

    return $loader;
  }

  public function setup($loader)
  {
    $this->init_wplocation_freetextformat_types();

    $loader->add_starter( new WPModulesAdminControl());
    $loader->add_starter( new PSR7AdminControl());
  }

  public function module_activate()
  {
  }

  public function module_deactivate()
  {
  }

  public function module_uninstall()
  {
  }

  private function init_wplocation_freetextformat_types()
  {
    array_push($this->_wplocation_freetextformattypes,
               new WPLocationFreeTextFormatType($this->get_id() . '-type', 
                                                $this->get_name() . ' Type',
                                                true));
    array_push($this->_wplocation_freetextformattypes,
               new WPLocationFreeTextFormatType('osm-type', 
                                                'OSM Nominatim Type'));
  }

  public function get_wplocation_freetextformat_types()
  {
    return $this->_wplocation_freetextformattypes;
  }

  public function is_wplocation_freetextformat_type_local($id)
  {
    return $this->get_id() . '-type' === trim($id);
  }

  public function is_wplocation_freetextformat_type_osm($id)
  {
    return 'osm-type' === trim($id);
  }

  public function get_developer_options_id()
  {
    return 'developer_options_enabled';
  }

  public function are_developer_options_enabled()
  {
    return get_option($this->get_developer_options_id(), false);
  }

  public function get_reset_log_manual_id()
  {
    return 'reset_log_manual';
  }

  public function is_reset_log_manual()
  {
    if(!$this->are_developer_options_enabled())
    {
      return false;
    }
    return get_option($this->get_reset_log_manual_id(), false);
  }

  public function get_manual_post_save_actions_id()
  {
    return 'manual_post_save_actions';
  }

  public function is_manual_post_save_actions()
  {
    if(!$this->are_developer_options_enabled())
    {
      return false;
    }
    return get_option($this->get_manual_post_save_actions_id(), false);
  }


}

$plugin = new WPKVMSyndicationToolboxPlugin('KVM Syndication Toolbox');
$plugin->register( __FILE__ , 10);
$plugin->set_description(
  'Dieses Modul hat verschiedenen Libraries die genutzt werden ' .
  'um die verschiedenen Funktionen zu aktivieren. Dieses Modul hat immer basis Funktionalitäten, wie ' .
  ' z.B. OSM Nominatim, Media Einstellungen.');



