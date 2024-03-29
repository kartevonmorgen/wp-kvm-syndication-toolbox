<?php

class SSUpdateFeed extends WPAbstractModuleProvider
                   implements UIPostTableActionIF
{
  public function action($post_id, $post)
  {
    echo '<p>Start updatefeed for ' . $post->post_title . '</p>';
    $instance = $this->get_current_module()->get_importer_factory();
    $importer = $instance->create_importer($post);
    $importer->set_echo_log(true);
    if(empty($importer))
    { 
      echo '<p>No Importer found for feed id' . $post_id . '</p>';
      return;
    }
    $importer->import();
    
    if($importer->has_error())
    {
      echo '<p>ERROR:' . $importer->get_error() . '</p>';
    }
    else
    {
      echo '<p>Import done succesfully' . $importer->get_log() . '</p>';
    }
    echo '</br>';
  }
}
