<?php

class UploadWPOrganisationToKVM
{
  private $_do_not_upload = false;
  private $_skip_in_memory_check = false;

  public function setup($loader)
  {
    $loader->add_action( 'save_post_organisation', $this, 'upload', 12, 3 );
  }

  public function set_do_not_upload($do_not_upload)
  {
    $this->_do_not_upload = $do_not_upload;
  }

  public function is_do_not_upload()
  {
    return $this->_do_not_upload;
  }

  public function set_skip_in_memory_check($skip_in_memory_check)
  {
    $this->_skip_in_memory_check = $skip_in_memory_check;
  }

  private function is_skip_in_memory_check()
  {
    return $this->_skip_in_memory_check;
  }


  function upload($organisation_post_id, $organisation_post, $update = false) 
  {
    $helper = new WPMetaFieldsHelper($organisation_post_id);
    $helper->add_field('organisation_kvm_id');
    $helper->add_field('organisation_kvm_log');
    $helper->add_field('organisation_type');
    $helper->add_field('organisation_firstname');
    $helper->add_field('organisation_lastname');
    $helper->add_field('organisation_phone');
    $helper->add_field('organisation_website');
    $helper->add_field('organisation_email');
    $helper->add_field('organisation_address');
    $helper->add_field('organisation_zipcode');
    $helper->add_field('organisation_city');
    $helper->add_field('organisation_lat');
    $helper->add_field('organisation_lng');
    if(!$this->is_skip_in_memory_check() && !$helper->has_in_memory_values())
    {
      // There are two save_post events,
      // We only want to upload to the KVM if there are
      // in momory values (_POST[key]) available
      return;
    }

    /*
    $logger = new PostMetaLogger(
      'organisation_kvm_log',
      $organisation_post_id, true);
    $logger->add_line('POSTID: ' . $organisation_post_id . ' UPDATE ' . $update . 
         ' DONOTUPLOAD: ' .$this->is_do_not_upload(). ' REQ ' . 
         $helper->get_value('organisation_website', $organisation_post_id));
    $logger->add_stacktrace();
    $logger->save();
    */

    if($this->is_do_not_upload())
    {
      return;
    }

    $mc = WPModuleConfiguration::get_instance();
    if (!$mc->is_module_enabled('wp-kvm-interface')) 
    { 
      echo '<p>Plugin Events KVM Interface not found</p>';
      return;
    }
    $kvminterface = $mc->get_module('wp-kvm-interface');

    if(empty($organisation_post))
    {
      return;
    }

    $wpOrganisation = new WPOrganisation();
    $this->fill_organisation_postmeta($helper, 
                                    $wpOrganisation, 
                                    $organisation_post);

    if(empty($organisation_post->post_status))
    {
      echo '<p>No post status found</p>';
      return;
    }

    if( $organisation_post->post_status !== 'publish')
    {
      // Only update if the post is published
      echo '<p>Die Organisation ist noch nicht veröffentlicht</p>';
      return;
    }

    $this->fill_organisation_post($wpOrganisation, 
                                $organisation_post);

    try
    {
      // Prevent unending loops, because the kvminterface make also saves.
      $this->set_do_not_upload(true);

      $kvm_id = $kvminterface->save_entry($wpOrganisation);

      update_post_meta($organisation_post_id, 'organisation_kvm_id', $kvm_id);

      // If the Address is changed, then there can be an update of the GEO
      // Coordinates, so we save them into the post
      $location = $wpOrganisation->get_location();
      update_post_meta($organisation_post_id, 'organisation_lat', $location->get_lat());
      update_post_meta($organisation_post_id, 'organisation_lng', $location->get_lon());
    }
    finally 
    {
      $this->set_do_not_upload(false);
    }
  }

  private function fill_organisation_postmeta($helper,
                                            $wpOrganisation, 
                                            $post)
  {
    $value = $helper->get_value('organisation_kvm_id');
    if( ! empty( $value))
    {
      $wpOrganisation->set_kvm_id( $value);
    }

    $value = $helper->get_value('organisation_type');
    $wpOrganisation->set_type_id($value);

    $value = $helper->get_value('organisation_firstname');
    if( ! empty($value ))
    {
      $wpOrganisation->set_contact_firstname($value);
    }

    $value = $helper->get_value( 'organisation_lastname');
    if( ! empty( $value))
    {
      $wpOrganisation->set_contact_lastname($value);
    }

    $value = $helper->get_value( 'organisation_phone');
    if( ! empty( $value))
    {
      $wpOrganisation->set_contact_phone($value);
    }

    $value = $helper->get_value( 'organisation_website');
    if( ! empty( $value))
    {
      $wpOrganisation->set_contact_website($value);
    }

    $value = $helper->get_value( 'organisation_email');
    if( ! empty( $value))
    {
      $wpOrganisation->set_contact_email($value);
    }

    $wpOrganisation->set_location(
      $this->create_location($helper, $post));
  }

  public function create_location($helper, $post)
  {
    if(empty($post))
    {
      return null;
    }

    $wpLocation = new WPLocation();
    if( ! empty( $post->post_title))
    {
      $wpLocation->set_name($post->post_title);
    }

    $value = $helper->get_value( 'organisation_address');
    if( ! empty( $value))
    {
      $wpLocHelper = new WPLocationHelper();
      $wpLocHelper->set_address($wpLocation, $value);
    }

    $value = $helper->get_value( 'organisation_zipcode');
    if( ! empty( $value))
    {
      $wpLocation->set_zip($value);
    }

    $value = $helper->get_value( 'organisation_city');
    if( ! empty($value))
    {
      $wpLocation->set_city($value);
    }

    /* 
     * Do not set them, but find them with OSM

    $value = $helper->get_value( 'organisation_lat');
    if( ! empty($value))
    {
      $wpLocation->set_lat($value);
    }

    $value = $helper->get_value( 'organisation_lng');
    if( ! empty($value))
    {
      $wpLocation->set_lon($value);
    }
     */

    return $wpLocation;
  }

  private function fill_organisation_post($wpOrganisation, 
                                        $organisation_post)
  {
    $wpOrganisation->set_id($organisation_post->ID);
    $wpOrganisation->set_user_id($organisation_post->post_author);
    $wpOrganisation->set_status($organisation_post->post_status);

    if(!empty($organisation_post->post_title))
    {
      $wpOrganisation->set_name(
        $organisation_post->post_title);
    }

    if(!empty($organisation_post->post_excerpt))
    {
      $wpOrganisation->set_description(
        $organisation_post->post_excerpt);
    }
    else if( !empty($organisation_post->post_content))
    {
      $wpOrganisation->set_description(
        wp_trim_excerpt('', $organisation_post));
    }
    else
    {
      $wpOrganisation->set_description('');
    }

    $wpOrganisation->set_origin_url(get_permalink($organisation_post));

    if(has_post_thumbnail($organisation_post))
    {
      $wpOrganisation->set_image_url( get_the_post_thumbnail_url($organisation_post) );
      $wpOrganisation->set_image_link_url( get_permalink($organisation_post) );
    }

    $posttags = get_the_tags($organisation_post->ID );
    if (!empty($posttags)) 
    {
       foreach($posttags as $tag) 
       {
         $wpOrganisation->add_tag(new WPTag($tag->name, 
                                          $tag->slug));
       }
    }
    $postcats = get_the_category($organisation_post->ID );
    if (!empty($postcats)) 
    {
       foreach($postcats as $cat) 
       {
         $wpOrganisation->add_category(
           new WPCategory($cat->name, $cat->slug));
       }
    }
  }
}
