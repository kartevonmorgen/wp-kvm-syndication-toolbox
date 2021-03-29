<?php

class DownloadWPOrganisationFromKVM
{
  public function download($organisation_post_id, $organisation_post)
  {
    $mc = WPModuleConfiguration::get_instance();
    if (!$mc->is_module_enabled('wp-kvm-interface')) 
    { 
      //echo 'Plugin Events KVM Interface not found';
      return;
    }
    $kvminterface = $mc->get_module('wp-kvm-interface');

    $post_meta = get_post_meta($organisation_post_id);
    if(empty($post_meta))
    {
      echo '<p>post_meta is empty for organisation_post_id:' . 
        $organisation_post_id . '</p>';
      return;
    }

    $kvm_id = reset($post_meta['organisation_kvm_id']);
    if(empty($kvm_id))
    {
      echo '<p>Karte von Morgen Entry Id not setted '.
        'in the for organisation_post_id:' . 
        $organisation_post_id . '</p>';
      return;
    }

    $wpOrganisationn = $kvminterface->get_entries_by_ids(
      array($kvm_id));

    $wpOrganisation = reset($wpOrganisationn);
    if(empty($wpOrganisation))
    {
      echo '<p>Keine Organisation gefunden für Karte von Morgen Entry Id ' .
        $kvm_id . '</p>';
      return;
    }


    $this->fill_organisation_postmeta($organisation_post_id, 
                                    $post_meta, 
                                    $wpOrganisation);
    $this->fill_organisation_post($organisation_post,
                                $wpOrganisation );
    echo '<p>Download Organisation from KVM for kvm_id ' . 
      $kvm_id . ' sucessfully </p>';
  }

  private function fill_organisation_postmeta($post_id,
                                            $post_meta,
                                            $wpOrganisation)
  {
    $args = array();


    /*
    if( ! empty( $wpOrganisation->get_type_id()))
    {
      $args['organisation_type'] = 
        $wpOrganisation->get_type_id();
    }

    if( ! empty( $wpOrganisation->get_contact_firstname()))
    {
      $args['organisation_firstname'] = 
        $wpOrganisation->get_contact_firstname(); 
    }

    if( ! empty( $wpOrganisation->get_contact_lastname()))
    {
      $args['organisation_lastname'] = 
        $wpOrganisation->get_contact_lastname(); 
    }

    if( ! empty( $wpOrganisation->get_contact_phone()))
    {
      $args['organisation_phone'] = 
        $wpOrganisation->get_contact_phone();
    }

    if( ! empty( $wpOrganisation->get_contact_website()))
    {
      $args['organisation_websitel'] = 
        $wpOrganisation->get_contact_website();
    }

    if( ! empty( $wpOrganisation->get_contact_email()))
    {
      $args['organisation_email'] = 
        $wpOrganisation->get_contact_email();
    }
    */

    $this->fill_location($wpOrganisation->get_location(), 
                         $post_id);
  }

  private function fill_location($wpLocation, $post_id)
  {
    $wpLocHelper = new WPLocationHelper();
    if( ! empty( $wpLocHelper->get_address($wpLocation)))
    {
      update_post_meta($post_id, 
                       'organisation_address',
                       $wpLocHelper->get_address($wpLocation));
    }

    if( ! empty( $wpLocation->get_zip()))
    {
      update_post_meta($post_id, 
                       'organisation_zipcode',
                       $wpLocation->get_zip());
    }

    if( ! empty( $wpLocation->get_city()))
    {
      update_post_meta($post_id, 
                       'organisation_city',
                       $wpLocation->get_city());
    }

    if( ! empty( $wpLocation->get_lat()))
    {
      update_post_meta($post_id, 
                       'organisation_lat', 
                       $wpLocation->get_lat());
    }

    if( ! empty( $wpLocation->get_lon()))
    {
      update_post_meta($post_id, 
                       'organisation_lng',
                       $wpLocation->get_lon());
    }

    return $wpLocation;
  }

  private function fill_organisation_post($organisation_post,
                                        $wpOrganisation )
  {
    $ipost = array();
    $ipost['ID'] = $organisation_post->ID;

    if(!empty($wpOrganisation->get_name()))
    {
      $ipost['post_title'] = 
        $wpOrganisation->get_name();
    }

    if(!empty($wpOrganisation->get_description()))
    {
      $ipost['post_content'] = 
        '<!-- wp:paragraph -->'.
        $wpOrganisation->get_description() .
        '<!-- /wp:paragraph -->';
    }

    wp_update_post( $ipost );

    $all_wp_cats = get_categories();
    $wpTagsStr = array();
    $wpCatsStr = array();
    foreach($wpOrganisation->get_tags() as $wpTag)
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

    wp_set_post_tags($organisation_post->ID, 
                     $wpTagsStr, 
                     true);
    wp_set_post_categories($organisation_post->ID, 
                           $wpCatsStr, 
                           true);
  }
}
