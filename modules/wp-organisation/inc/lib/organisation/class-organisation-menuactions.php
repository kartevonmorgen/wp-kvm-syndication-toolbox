<?php

class OrganisationMenuActions extends WPAbstractModuleProvider 
{
  private $_kvm_uploader;

  public function __construct($current_module, $kvm_uploader)
  {
    parent::__construct($current_module);
    $this->_kvm_uploader = $kvm_uploader;
  }

  public function get_kvm_uploader()
  {
    return $this->_kvm_uploader;
  }

  public function setup($loader)
  {
    $this->reset_log_action($loader);

    // Only make the upload option available if we 
    // have this devleoper setting setted.
    $root = $this->get_root_module();
    if($root->is_manual_post_save_actions())
    {
      $tableAction = new UIPostTableAction('upload-to-kvm', 
                                           'Upload zu der Karte von morgen', 
                                           'Upload zu KVM', 
                                           'organisation',
                                           'Organisation');
      $tableAction->set_postaction_listener(
        new KVMUploadAction($this->get_kvm_uploader()));
      $tableAction->setup($loader);
    }
    
    // Only Admins can download from the KVM
    if(!current_user_can('manage_options'))
    {
      return;
    }

    // Download
    $tableAction = new UIPostTableAction('download-from-kvm', 
                                         'Download von der Karte von morgen', 
                                         'Download von KVM', 
                                         'organisation',
                                         'Organisation');
    $field = new UIMetaboxField('organisation_kvm_id', 'KVM Id');
    $field->set_description('Download Organisation für dieser KVM Id');
    $tableAction->add_field($field);
    $tableAction->set_postaction_listener(
      new KVMDownloadAction($this->get_kvm_uploader()));
    $tableAction->setup($loader);
  }

  private function reset_log_action($loader)
  {
    if(!current_user_can('manage_options'))
    {
      return;
    }

    $root = $this->get_root_module();
    if(!$root->is_reset_log_manual())
    {
      return;
    }

    // Reset KVM Log
    $tableAction = new UIPostTableAction('reset-kvm-log', 
                                         'Reset KVM Log', 
                                         'Reset KVM Log', 
                                         'organisation',
                                         'Organisation');
    $tableAction->set_postaction_listener(new class() implements UIPostTableActionIF
      {
        public function action($post_id, $post)
        {
          $logger = new PostMetaLogger(
             'organisation_kvm_log',
             $post_id);
          $logger->add_line('');
          $logger->save();
        }
      });
    $tableAction->setup($loader);
  }
}

abstract class KVMAction implements UIPostTableActionIF
{
  private $_kvm_uploader;

  public function __construct($kvm_uploader)
  {
    $this->_kvm_uploader = $kvm_uploader;
  }

  public function get_kvm_uploader()
  {
    return $this->_kvm_uploader;
  }

  public abstract function action($post_id, $post);
}

class KVMUploadAction extends KVMAction
{
  public function action($post_id, $post)
  {
    $this->get_kvm_uploader()->set_skip_in_memory_check(true);
    $this->get_kvm_uploader()->upload($post_id, $post);
    $this->get_kvm_uploader()->set_skip_in_memory_check(false);
  }
}

class KVMDownloadAction extends KVMAction
{
  public function action($post_id, $post)
  {
    try
    {
      // The KVM Uploader will automtically start
      // if fields are changed on the Organisation.
      // We do not want to upload if we have just downloaded.
      $this->get_kvm_uploader()->set_do_not_upload(true);
    
      $downloader = new DownloadWPOrganisationFromKVM();
      $downloader->download($post_id, $post, $kvm_id); 
    }
    finally
    {
      $this->get_kvm_uploader()->set_do_not_upload(false);
    }
  }
}
