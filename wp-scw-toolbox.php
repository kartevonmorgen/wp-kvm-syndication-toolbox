<?php
/*
Plugin Name: WP KVM Syndication Toolbox
Plugin URI: https://github.com/kartevonmorgen/wp-kvm-syndication-toolbox
Description: Tools for Transition
Version: 1.5
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

// Include all Modules
$modules_path = dirname( __FILE__ ) . '/modules';
foreach ( glob( "$modules_path/*/*.php" ) as $filename )
{
  include_once $filename;
}

class WPSCWToolboxPlugin extends WPAbstractPlugin
{
  private $_wplocation_freetextformattypes = array();

  public function __construct($title)
  {
    parent::__construct($title);
    $this->set_description(
      'Dieses Modul hat verschiedenen Libraries die genutzt werden ' .
      'um die verschiedenen Funktionen zu aktivieren. ' . 
      'Dieses Modul hat immer     basis Funktionalitäten, wie ' .
      ' z.B. OSM Nominatim, Media Einstellungen.');
  }

  public function setup_modules()
  {
    $modules = array();
    $modules[get_class($this)] = $this;

    $classnames = get_declared_classes();
    foreach($classnames as $classname)
    {
      if( $classname == "WPAbstractPlugin" )
      {
        continue;
      }

      if( is_subclass_of( $classname, 'WPAbstractPlugin'))
      {
        continue;
      }

      if( is_subclass_of( $classname, 'WPAbstractModule'))
      {
        $module = new $classname();
        $modules[$classname] = $module;
      }
    }

    foreach($modules as $module)
    {
      $parent = $module->get_parent_classname();
      if(!empty($parent))
      {
        $modules[$parent]->add_module($module);
      }
    }
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

    // -- Module Provider --
    $loader->add_include('/inc/lib/module/class-wp-abstractmoduleprovider.php');
    
    // -- WP Helper Classes --
    $loader->add_include('/inc/lib/wp/class-wp-abstractposttype.php');
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

$plugin = new WPSCWToolboxPlugin('WP KVM Syndication Toolbox');
$plugin->register( __FILE__ , 0);

