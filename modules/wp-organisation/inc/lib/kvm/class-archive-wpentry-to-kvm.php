<?php

class ArchiveWPEntryToKVM
  extends WPAbstractModuleProvider
{
  public function setup($loader)
  {
    $loader->add_action('trashed_post', 
                        $this, 
                        'trashed_post');
  }

  public function get_type()
  {
    return $this->get_current_module()->get_type();
  }

  public function create_type()
  {
    $clazz = $this->get_type()->get_clazz();
    return new $clazz();
  }

  public function trashed_post( $post_id )
  {
    $post = get_post( $post_id );
    if(empty($post))
    {
      return;
    }

    $type = $this->get_type();
    if($type->get_id() !== $post->post_type)
    {
      return;
    }

    $this->archive_entry($post_id, $post);
  }



  /** 
   * This can be used to bring a entry back from the KVM. 
   * Is not in use yet.
   */
  public function confirm_entry($entry_post_id, 
                                $entry_post)
  {
    if (!$this->is_module_enabled('wp-kvm-interface')) 
    { 
      echo '<p>Plugin Events KVM Interface is not enabled</p>';
      return;
    }

    $helper = $this->create_helper();
    $wpEntry = $this->create_type();
    $this->fill_entry_postmeta($helper, 
                               $wpEntry, 
                               $entry_post);
    $this->fill_entry_post($wpEntry, 
                           $entry_post);

    if(empty($wpEntry->get_kvm_id()))
    {
      echo '<p>There is no KVM Id for this ' . $wpEntry->get_type() . '</p>';
      return;
    }

    $kvminterface = $this->get_module('wp-kvm-interface');
    $kvminterface->confirm_entry($wpEntry);
  }

  /**
   * Archive an Entry to the KVM, so it is no longer visible there
   */
  public function archive_entry($entry_post_id, 
                                $entry_post)
  {
    if (!$this->is_module_enabled('wp-kvm-interface')) 
    { 
      echo '<p>Plugin Events KVM Interface is not enabled</p>';
      return;
    }

    $helper = $this->create_helper();
    $wpEntry = $this->create_type();
    $this->fill_entry_postmeta($helper, 
                               $wpEntry, 
                               $entry_post);
    $this->fill_entry_post($wpEntry, 
                           $entry_post);

    if(empty($wpEntry->get_kvm_id()))
    {
      echo '<p>There is no KVM Id for this ' . $wpEntry->get_type() . '</p>';
      return;
    }

    $kvminterface = $this->get_module('wp-kvm-interface');
    $kvminterface->archive_entry($wpEntry);
  }

  private function create_helper()
  {
    $helper = new WPMetaFieldsHelper($entry_post_id);
    $type = $this->get_type();
    $helper->set_prefix($type->get_id());
    $helper->add_field('_kvm_id');
    $helper->add_field('_kvm_log');
    return $helper;
  }

  private function fill_entry_postmeta($helper,
                                       $wpEntry, 
                                       $post)
  {
    $value = $helper->get_value('_kvm_id');
    if( ! empty( $value))
    {
      $wpEntry->set_kvm_id( $value);
    }
  }

  private function fill_entry_post($wpEntry, 
                                   $entry_post)
  {
    $wpEntry->set_id($entry_post->ID);
    $wpEntry->set_user_id($entry_post->post_author);
    $wpEntry->set_status($entry_post->post_status);

    if(!empty($entry_post->post_title))
    {
      $wpEntry->set_name(
        $entry_post->post_title);
    }
  }

}
