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

    // Duplicate Status
    $tableAction = new UIPostTableAction('status-duplicate-timeframe', 
                                         'Status Duplizier Zeitrahmen', 
                                         'Status Zeitrahmen duplizieren', 
                                         'cb_timeframe',
                                         'Zeitrahemen');
    $tableAction->set_post_status('draft');
    $tableAction->set_parent_menu('admin.php', 'cb-dashboard');
    $tableAction->set_postaction_listener(new class() implements UIPostTableActionIF
      {
        public function action($post_id, $post)
        {
          echo '<p><b>Log für ' . $post->post_title . '</b></p>';
          echo '' . $post->post_content . '';
          echo '<p><b>IDS für ' . $post->post_title . '</b></p>';
          echo '' . $post->post_excerpt . '';
        }
      });
    $tableAction->setup($loader);

    // Stop duplizieren
    $tableAction = new UIPostTableAction('stop-duplicate-timeframe', 
                                         'Stop Duplizier Zeitrahmen', 
                                         'Stop Zeitrahmen duplizieren', 
                                         'cb_timeframe',
                                         'Zeitrahemen');
    $tableAction->set_post_status('draft');
    $tableAction->set_parent_menu('admin.php', 'cb-dashboard');
    $tableAction->set_postaction_listener(new class() implements UIPostTableActionIF
      {
        public function action($post_id, $post)
        {
          echo '<p><b>Stop duplizieren für ' . $post->post_title . '</b></p>';
          $content = $post->post_content;
          $content .= 'Duplizieren abgebrochen!!';
          $args = array(
            'ID' => $post_id,
            'post_excerpt' => 'READY',
            'post_content' => $content);
          wp_update_post($args);
          echo '<p>Fertig</p>';
          echo '<p><b>Log für ' . $post->post_title . '</b></p>';
          echo '' . $content . '';
        }
      });
    $tableAction->setup($loader);

    // Delete Duplicated
    $tableAction = new UIPostTableAction('delete-duplicate-timeframe', 
                                         'Duplizierte Zeitrahmen entfernen', 
                                         'Entfernen dupliziierte Zeitrahmen', 
                                         'cb_timeframe',
                                         'Zeitrahemen');
    $tableAction->set_post_status('draft');
    $tableAction->set_parent_menu('admin.php', 'cb-dashboard');
    $tableAction->set_postaction_listener(new class() implements UIPostTableActionIF
      {
        public function action($post_id, $post)
        {
          $job = new DuplicateTimeFrame();
          $job->delete_frontend($post); 
        }
      });
    $tableAction->setup($loader);

    // Duplicate
    $tableAction = new UIPostTableAction('duplicate-timeframe', 
                                         'Duplizier Zeitrahmen', 
                                         'Zeitrahmen duplizieren', 
                                         'cb_timeframe',
                                         'Zeitrahemen');
    $tableAction->set_post_status('draft');
    $tableAction->set_parent_menu('admin.php', 'cb-dashboard');
    $tableAction->set_postaction_listener(new class() implements UIPostTableActionIF
      {
        public function action($post_id, $post)
        {
          $job = new DuplicateTimeFrame();
          $job->duplicate_frontend($post); 
        }
      });
    $tableAction->setup($loader);
  }
}
