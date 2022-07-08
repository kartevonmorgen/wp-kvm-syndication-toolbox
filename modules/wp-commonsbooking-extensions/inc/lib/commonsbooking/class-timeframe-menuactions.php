<?php

class TimeFrameMenuActions
{
  public function __construct()
  {
  }

  public function setup($loader)
  {
    $this->timeFrame_action($loader);

  }

  private function timeFrame_action($loader)
  {
    if(!current_user_can('manage_options'))
    {
      return;
    }

    // Duplicate
    $tableAction = new UIPostTableAction('duplicate-timeframe', 
                                         'Duplizier Zeitrahmen für alle Artikel', 
                                         'Zeitrahmen duplizieren', 
                                         'cb_timeframe',
                                         'Zeitrahemen');
    $tableAction->set_parent_menu('admin.php', 'cb-dashboard');
    $tableAction->set_postaction_listener(new class() implements UIPostTableActionIF
      {
        public function action($post_id, $post)
        {
          $job = new DuplicateTimeFrame();
          $job->duplicate($post); 
        }
      });
    $tableAction->setup($loader);
  }
}
