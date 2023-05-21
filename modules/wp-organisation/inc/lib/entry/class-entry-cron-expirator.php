<?php

class EntryCronExpirator
  extends AbstractCronJob
{
  public function get_type()
  {
    return $this->get_current_module()->get_type();
  }

  public function get_type_id()
  {
    return $this->get_type()->get_id();
  }

  public function execute()
  {
    $module = $this->get_current_module();

    $message = '';
    $message .= 'Start check on expiration of projects ' . get_date_from_gmt(date("Y-m-d H:i:s"));
    $message .= PHP_EOL;
    $message .= '  Unix timestamp: ' . time();
    $message .= PHP_EOL;
    update_option($module->get_cron_expiration_messages_id(), 
                  $message );

    $args = array(
      'numberposts'   =>  -1,
      'post_type'     =>  $this->get_type_id(),
      'post_statis'   =>  'publish',
      'meta_query' => array(
        'relation' => 'AND',
		    'enabled_clause' => array(
			    'key'   => $this->get_type_id() . '_expiration_enabled',
          'value' => 'on'
        ),
		    'date_clause' => array(
          'key'   => $this->get_type_id() . '_expiration_date',
          'compare' => '<',
          'value' => time()
        )
      )
		);
    $entries = get_posts( $args );
    if(empty($entries))
    {
      $message .= 'No projects found which has been expired, ' . 
                  'nothing to do';
      $message .= PHP_EOL;
      update_option($module->get_cron_expiration_messages_id(), 
                    $message );
      return;
    }

    foreach($entries as $entry)
    {
      $message .= PHP_EOL;
      $message .= '' . $entry->post_title . 
                  ' (ID=' . $entry->ID . ') has ' . 
                  'been expired, so we set it to draft.';
      $message .= PHP_EOL;
      update_option($module->get_cron_expiration_messages_id(), 
                    $message );

      $data = array(
                'ID' => $entry->ID,
                'post_status' => 'draft');
      wp_update_post( $data );
    }
    
    $message .= 'Expiration job done. ';
    $message .= PHP_EOL;
    update_option($module->get_cron_expiration_messages_id(), 
                  $message );
  }
}

