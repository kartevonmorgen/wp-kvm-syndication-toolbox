<?php
/**
  * Controller GraphQLAdminControl
  * Settings page of the GraphQL Events Server.
  * It uses the Interface Events API to retrieve the events
  * from the active event calendar 
  *
  * @author  	Sjoerd Takken
  * @copyright 	No Copyright.
  * @license   	GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
  */
class GraphQLAdminControl extends UIAbstractAdminControl
                          implements WPModuleStarterIF
{
  public function start() 
  {
    $rootmodule = $this->get_root_module();
    $thismodule = $this->get_current_module();

    $page = new UISettingsPage('graphql-events-server', 
                               'GraphQL events server settings');
    $page->set_submenu_page(true, $rootmodule->get_id() . '-menu');
    $section = $page->add_section('graphql_section_one', 'Header elements');
    $section->set_description(
      'This section defines the header elements of your feeds. '.
      'Those elements will be read by search engines to identify'. 
      'the origin of the events.');

    $section->add_textfield($thismodule->get_graphql_config_name_id(), 
                            'GraphQL Instance name');
    $section->add_textfield($thismodule->get_graphql_config_description_id(), 
                            'GraphQL Instance description');

    $page->register();
  }
}
