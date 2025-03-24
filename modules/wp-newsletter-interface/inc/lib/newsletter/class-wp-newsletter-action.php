<?php

class WPNewsletterAction extends WPAbstractModuleProvider
                   implements UIPostTableActionIF
{
  private $activate = false;

  public function __construct($module, $activate = false)
  {
    parent::__construct($module);
    $this->activate = $activate;
  }

  public function action($post_id, $post)
  {
    if($this->activate)
    {
      echo '<p>Activate Newsletter List: ' . $post->post_title . '</p>';
      update_post_meta($post_id, 'newsletterlist_sendto', 'on');
    }
    else
    {
      echo '<p>Deactivate Newsletter List: ' . $post->post_title . '</p>';
      update_post_meta($post_id, 'newsletterlist_sendto', 'off');
    }
  }
}
