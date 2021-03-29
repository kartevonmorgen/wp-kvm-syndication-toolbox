<?php

class InitiativeMenuActions
{
  public function __construct()
  {
  }

  public function setup($loader)
  {
    $this->migrate_action($loader);

  }

  private function migrate_action($loader)
  {
    $mc = WPModuleConfiguration::get_instance();
    if(! $mc->is_module_enabled('wp-user-register'))
    {
      return;
    }

    if(!current_user_can('manage_options'))
    {
      return;
    }

    // Upload
    $tableAction = new UIPostTableAction('migrate-user-und-initiative', 
                                         'Migration Benutzer und Initiative', 
                                         'Benutzer und Initiative Migration', 
                                         'initiative',
                                         'Initiative');
    $tableAction->set_postaction_listener(new class() implements UIPostTableActionIF
      {
        public function action($post_id, $post)
        {
          $job = new MigrateUserUndInitiative();
          $job->migrate($post); 
        }
      });
    $tableAction->setup($loader);
  }
}
