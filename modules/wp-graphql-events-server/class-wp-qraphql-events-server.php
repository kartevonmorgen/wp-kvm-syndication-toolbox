<?php
/*
 * GraphQL API based on the GraphQL API used 
 * in Mobilizon
 * The Plugin WPGraphQL is needed for function
*/

class WPGraphQLEventsServerModule extends WPAbstractModule
                                  implements WPModuleStarterIF
{
  public function __construct()
  {
    parent::__construct('GraphQL events server');
    $this->set_description('Das Modul kann ' . 
                           'benutzt werden um Veranstaltungen ' .
                           'als GraphQL zur Verführung ' .
                           'zu stellen. Die können dann auf ' .
                           'eine andere Webseite mit dem ' .
                           'Events feed importer ' .
                           'importiert werden über GraphQL'.
                           'Der GraphQL API ist bassiert auf'.
                           'Mobilizon und dafür ist'.
                           'der WPGraphQL Plugin notwendig');
  }

  public function setup_includes($loader)
  {
    $loader->add_include('/inc/lib/graphql/class-graphql-register-search-events.php' );
    $loader->add_include('/admin/inc/controllers/class-graphql-admincontrol.php' );
  }

  public function setup($loader)
  {
    $feedhandler = new GraphQLRegisterSearchEvents($this);
    $feedhandler->setup($loader);

    $loader->add_starter(new GraphQLAdminControl($this));
  }

  public function start()
  {
    // Start UI Part
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

  public function get_parent_classname()
  {
    return 'WPEventsInterfaceModule';
  }

  public function get_graphql_config_name_id()
  {
    return 'graphql_config_name';
  }

  public function get_graphql_config_name()
  {
    return get_option($this->get_graphql_config_name_id());
  }

  public function get_graphql_config_description_id()
  {
    return 'graphql_config_description';
  }

  public function get_graphql_config_description()
  {
    return get_option($this->get_graphql_config_description_id());
  }

}
