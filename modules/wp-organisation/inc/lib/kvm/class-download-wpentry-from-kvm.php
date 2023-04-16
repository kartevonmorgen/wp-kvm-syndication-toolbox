<?php

class DownloadWPEntryFromKVM
  extends WPAbstractModuleProvider
{
  public function get_type()
  {
    return $this->get_current_module()->get_type();
  }

  public function download($entry_post_id, $entry_post)
  {
    if (!$this->is_module_enabled('wp-kvm-interface')) 
    { 
      //echo 'Plugin Events KVM Interface not found';
      return;
    }
    $kvminterface = $this->get_module('wp-kvm-interface');

    $post_meta = get_post_meta($entry_post_id);
    if(empty($post_meta))
    {
      echo '<p>post_meta is empty for entry_post_id:' . 
        $entry_post_id . '</p>';
      return;
    }

    $kvm_id = reset($post_meta['entry_kvm_id']);
    if(empty($kvm_id))
    {
      echo '<p>Karte von Morgen Entry Id not setted '.
        'in the for entry_post_id:' . 
        $entry_post_id . '</p>';
      return;
    }

    $wpEntries = $kvminterface->get_entries_by_ids(
      array($kvm_id));

    $wpEntry = reset($wpEntries);
    if(empty($wpEntry))
    {
      echo '<p>Kein ' . $this->get_type() . ' gefunden für Karte von Morgen Entry Id ' .
        $kvm_id . '</p>';
      return;
    }

    //TODO: Do Check on if it is a Project or Organisation
    $this->fill_entry_postmeta($entry_post_id, 
                               $post_meta, 
                               $wpEntry);
    $this->fill_entry_post($entry_post,
                           $wpEntry );
    echo '<p>Download ' . $this->get_type() . ' from KVM for kvm_id ' . 
      $kvm_id . ' sucessfully </p>';
  }

  private function fill_entry_postmeta($post_id,
                                       $post_meta,
                                       $wpEntry)
  {
    $slug = $this->get_type()->get_id();
    $args = array();


    /*
    if( ! empty( $wpEntry->get_type_type_id()))
    {
      $args[$slug . '_type'] = 
        $wpEntry->get_type_type_id();
    }

    if( ! empty( $wpEntry->get_contact_firstname()))
    {
      $args[$slug . '_firstname'] = 
        $wpEntry->get_contact_firstname(); 
    }

    if( ! empty( $wpEntry->get_contact_lastname()))
    {
      $args[$slug . '_lastname'] = 
        $wpEntry->get_contact_lastname(); 
    }

    if( ! empty( $wpEntry->get_contact_phone()))
    {
      $args[$slug . '_phone'] = 
        $wpEntry->get_contact_phone();
    }

    if( ! empty( $wpEntry->get_contact_website()))
    {
      $args[$slug . '_websitel'] = 
        $wpEntry->get_contact_website();
    }

    if( ! empty( $wpEntry->get_contact_email()))
    {
      $args[$slug . '_email'] = 
        $wpEntry->get_contact_email();
    }
    */

    $this->fill_location($wpEntry->get_location(), 
                         $post_id);
  }

  private function fill_location($wpLocation, $post_id)
  {
    $slug = $this->get_type()->get_id();

    $wpLocHelper = new WPLocationHelper();
    if( ! empty( $wpLocHelper->get_address($wpLocation)))
    {
      update_post_meta($post_id, 
                       $slug . '_address',
                       $wpLocHelper->get_address($wpLocation));
    }

    if( ! empty( $wpLocation->get_zip()))
    {
      update_post_meta($post_id, 
                       $slug . '_zipcode',
                       $wpLocation->get_zip());
    }

    if( ! empty( $wpLocation->get_city()))
    {
      update_post_meta($post_id, 
                       $slug . '_city',
                       $wpLocation->get_city());
    }

    if( ! empty( $wpLocation->get_lat()))
    {
      update_post_meta($post_id, 
                       $slug . '_lat', 
                       $wpLocation->get_lat());
    }

    if( ! empty( $wpLocation->get_lon()))
    {
      update_post_meta($post_id, 
                       $slug . '_lng',
                       $wpLocation->get_lon());
    }

    return $wpLocation;
  }

  private function fill_entry_post($entry_post,
                                   $wpEntry )
  {
    $ipost = array();
    $ipost['ID'] = $entry_post->ID;

    if(!empty($wpEntry->get_name()))
    {
      $ipost['post_title'] = 
        $wpEntry->get_name();
    }

    if(!empty($wpEntry->get_description()))
    {
      $ipost['post_content'] = 
        '<!-- wp:paragraph -->'.
        $wpEntry->get_description() .
        '<!-- /wp:paragraph -->';
    }

    wp_update_post( $ipost );

    $all_wp_cats = get_categories();
    $wpTagsStr = array();
    $wpCatsStr = array();
    foreach($wpEntry->get_tags() as $wpTag)
    {
      $add_cat = false;
      foreach($all_wp_cats as $wp_cat)
      {
        if( $wpTag->get_slug() == $wp_cat->slug )
        {
          $add_cat = true;
          break;
        }
      }
      
      if($add_cat)
      {
        array_push( $wpCatsStr, $wpTag->get_slug());
      }
      else
      {
        array_push( $wpTagsStr, $wpTag->get_slug());
      }
    }

    wp_set_post_tags($entry_post->ID, 
                     $wpTagsStr, 
                     true);
    wp_set_post_categories($entry_post->ID, 
                           $wpCatsStr, 
                           true);
  }
}
