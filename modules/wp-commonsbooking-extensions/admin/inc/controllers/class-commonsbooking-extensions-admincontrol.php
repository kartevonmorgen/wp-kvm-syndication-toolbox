<?php

/**
 * Controller CommonsBookingExtensionsAdminControl
 *
 * @author   Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class CommonsBookingExtensionsAdminControl extends UIAbstractAdminControl 
                               implements WPModuleStarterIF
{
  public function start() 
  {
    $mc = WPModuleConfiguration::get_instance();
    $rootmodule = $mc->get_root_module();

    $page = new UISettingsPage('cb-extensions-options', 
                               'Commons Booking Extensions settings');
    $page->set_submenu_page(true, $rootmodule->get_id() . '-menu');
    $section = $page->add_section('wplib_section_one', 'Commons Booking Extensions settings');
    $section->set_description('Hier sind keine Einstellungen ' .
                              'Man erstellt ein Zeitrahmen ohne ein Artikel zu wahlen ' .
                              'in Entwurf Modus. Dann wahlt man die Aktion '. 
                              '"Duplizier Zeitrahmen für alle Artikel" ' . 
                              'Dann werden erst alle Zeitrahmen für diese Standort '.
                              'entfernt und gleich danach wieder erstellt aus dem ' .
                              'Zeitrahmen ohne Artikel in Entwurf Modus');

    $page->register();
  }
}
