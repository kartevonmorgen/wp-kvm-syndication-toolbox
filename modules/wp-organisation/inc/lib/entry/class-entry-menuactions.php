<?php

class EntryMenuActions extends WPAbstractModuleProvider 
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
    $current_module = $this->get_current_module(); 
    $type = $current_module->get_type();

    $this->reset_log_action($loader);

    // Only make the upload option available if we 
    // have this devleoper setting setted.
    $root = $this->get_root_module();
    if($root->is_manual_post_save_actions())
    {
      $tableAction = new UIPostTableAction($type->get_id() . '-upload-to-kvm', 
                                           'Upload zu der Karte von morgen', 
                                           'Upload zu KVM', 
                                           $type->get_id(),
                                           $type->get_title());
      $tableAction->set_postaction_listener(
        new KVMUploadAction($current_module,
                            $this->get_kvm_uploader()));
      $tableAction->setup($loader);

      $tableAction = new UIPostTableAction($type->get_id() . '-archive-to-kvm', 
                                           'Archivieren zu der Karte von morgen', 
                                           'Archieveren zu KVM', 
                                           $type->get_id(),
                                           $type->get_title());
      $tableAction->set_postaction_listener(
        new KVMArchiveAction($current_module,
                             $this->get_kvm_uploader()));
      $tableAction->setup($loader);
    }

    // Only Admins can download from the KVM for Organisations
    // for Projects everyone can do.
    if($type->get_id() === WPEntryType::ORGANISATION
      && !current_user_can('manage_options'))
    {
      return;
    }

    // Download
    $tableAction = new UIPostTableAction($type->get_id() . '-download-from-kvm', 
                                         'Download von der Karte von morgen', 
                                         'Download von KVM', 
                                         $type->get_id(),
                                         $type->get_title());

    $field = new KVMEntriesDropDownField($type->get_id() . '_kvm_id', 'KVM Id',$this);
    $field->set_description('Download ' . $type . ' für dieser KVM Id');
    
    $tableAction->add_field($field);
    $tableAction->set_postaction_listener(
      new KVMDownloadAction($this->get_current_module(),
                            $this->get_kvm_uploader()));
    if(WPEntryType::PROJECT == $type->get_id())
    {
      $tableAction->set_create_post(true);
    }
    $tableAction->setup($loader);
  }

  private function reset_log_action($loader)
  {
    if(!current_user_can('manage_options'))
    {
      return;
    }

    $type = $this->get_current_module()->get_type();
    $root = $this->get_root_module();
    if(!$root->is_reset_log_manual())
    {
      return;
    }

      // Reset KVM Log
      $tableAction = new UIPostTableAction($type->get_id() . '-reset-kvm-log',  
                                         'Reset KVM Log', 
                                         'Reset KVM Log', 
                                         $type->get_id(),
                                         $type->get_title());
    $tableAction->set_postaction_listener(new class() implements UIPostTableActionIF
      {
        public function action($post_id, $post)
        {
          $logger = new PostMetaLogger(
             $post->post_type . '_kvm_log',
             $post_id);
          $logger->add_line('');
          $logger->save();
        }
      });
    $tableAction->setup($loader);
  }
}

class KVMEntriesDropDownField extends UIMetaboxDropDownField
{
  private $_parent;

  public function __construct($id, $title, $parent)
  {
    parent::__construct($id, $title);
    $this->_parent = $parent;
  }

  public function get_parent()
  {
    return $this->_parent;
  }

  public function get_values()
  {
    $parent = $this->get_parent();
    if (!$parent->is_module_enabled('wp-kvm-interface')) 
    { 
      return parent::get_values();
    }
    $kvminterface = $parent->get_module('wp-kvm-interface');
    
    if(empty(parent::get_values()))
    {
      $entries = $kvminterface->get_entries_by_bbox_for_region();
      foreach($entries as $entry)
      {
        if($entry->has_fixed_tag())
        {
          continue;
        }
        $this->add_value( $entry->get_id(), 
          $entry->get_title() . ' (' . $entry->get_id() . ')');
      }
    } 
    return parent::get_values();
  }
}

abstract class KVMAction implements UIPostTableActionIF
{
  private $_current_module;
  private $_kvm_uploader;

  public function __construct($module, $kvm_uploader)
  {
    $this->_current_module = $module;
    $this->_kvm_uploader = $kvm_uploader;
  }

  public function get_kvm_uploader()
  {
    return $this->_kvm_uploader;
  }

  public function get_current_module()
  {
    return $this->_current_module;
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

class KVMArchiveAction extends KVMAction
{
  public function action($post_id, $post)
  {
    $module = $this->get_current_module();
    $archiver = new ArchiveWPEntryToKVM( $module );
    $archiver->archive_entry($post_id, 
                             $post, 
                             'Automatically archived by ' . 
                             get_site_url() . 
                             'Wordpress ' . $post->post_type . 
                             ' (WP.ID=' . $post_id );
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

      $module = $this->get_current_module();
      $downloader = new DownloadWPEntryFromKVM( $module );
      $downloader->download($post_id, $post); 
    }
    finally
    {
      $this->get_kvm_uploader()->set_do_not_upload(false);
    }
  }
}
