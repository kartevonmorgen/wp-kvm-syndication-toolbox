<?php

class UploadWPEntryToKVM
  extends WPAbstractModuleProvider
{
  private $_do_not_upload = false;
  private $_skip_in_memory_check = false;

  public function get_type()
  {
    return $this->get_current_module()->get_type();
  }

  public function setup($loader)
  {
    $loader->add_action( 'save_post_' . $this->get_type()->get_id(), 
                         $this, 
                         'upload', 
                         12, 
                         3 );
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


  function upload($entry_post_id, 
                  $entry_post, 
                  $update = false) 
  {
    $helper = new WPMetaFieldsHelper($entry_post_id);
    $helper->set_prefix($this->get_type()->get_id());
    $helper->add_field('_kvm_id');
    $helper->add_field('_kvm_log');
    $helper->add_field('_kvm_do_not_upload');
    $helper->add_field('_type');
    $helper->add_field('_firstname');
    $helper->add_field('_lastname');
    $helper->add_field('_phone');
    $helper->add_field('_website');
    $helper->add_field('_email');
    $helper->add_field('_address');
    $helper->add_field('_zipcode');
    $helper->add_field('_city');
    $helper->add_field('_lat');
    $helper->add_field('_lng');
    if(!$this->is_skip_in_memory_check() && 
       !$helper->has_in_memory_values())
    {
      // There are two save_post events,
      // We only want to upload to the KVM if there are
      // in momory values (_POST[key]) available
      return;
    }

    /*
    $logger = new PostMetaLogger(
      $this->get_type()->get_id() . '_kvm_log',
      $entry_post_id, true);
    $logger->add_line('POSTID: ' . $entry_post_id . ' UPDATE ' . $update . 
         ' DONOTUPLOAD: ' .$this->is_do_not_upload(). ' REQ ' . 
         $helper->get_value('_website', $entry_post_id));
    $logger->add_stacktrace();
    $logger->save();
    */

    if($this->is_do_not_upload())
    {
      return;
    }

    if (!$this->is_module_enabled('wp-kvm-interface')) 
    { 
      echo '<p>Plugin Events KVM Interface not found</p>';
      return;
    }
    $kvminterface = $this->get_module('wp-kvm-interface');

    if(empty($entry_post))
    {
      return;
    }

    // TODO: If we are a project, we have to look if the
    //       Organisation is allowed vor upload
    $value = $helper->get_value('_kvm_do_not_upload');
    if( $value == true )
    {
      echo "<p>" . $this->get_type() . 
           " wird nicht hochgeladen !!<br>" . 
           "Nicht hochladen zu der Karte von morgen ist aktiviert.</p>";
      return;
    }

    $wpEntry = $this->create_type();
    $this->fill_entry_postmeta($helper, 
                               $wpEntry, 
                               $entry_post);

    if(empty($entry_post->post_status))
    {
      echo '<p>No post status found</p>';
      return;
    }

    if( $entry_post->post_status !== 'publish')
    {
      // Only update if the post is published
      echo '<p>' . $this->get_type() . 
        ' ist noch nicht veröffentlicht</p>';
      return;
    }

    $this->fill_entry_post($wpEntry, 
                           $entry_post);

    try
    {
      // Prevent unending loops, because the kvminterface make also saves.
      $this->set_do_not_upload(true);

      $kvm_id = $kvminterface->save_entry($wpEntry);

      update_post_meta($entry_post_id, $this->get_type()->get_id() . 
                                       '_kvm_id', $kvm_id);

      // If the Address is changed, then there can be an update of the GEO
      // Coordinates, so we save them into the post
      $location = $wpEntry->get_location();
      update_post_meta($entry_post_id, 
                       $this->get_type()->get_id() . '_lat', 
                       $location->get_lat());
      update_post_meta($entry_post_id, 
                       $this->get_type()->get_id() . '_lng', 
                       $location->get_lon());
      echo '<p>Hochladen erfolgreich (KVM Id: ' . 
           $kvm_id . ')</p>';
    }
    finally 
    {
      $this->set_do_not_upload(false);
    }
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

    // TODO: We do not have this for Projects, 
    //       it is a fixed Type 'Initiative'
    $value = $helper->get_value('_type');
    $wpEntry->set_type_type_id($value);

    $value = $helper->get_value('_firstname');
    if( ! empty($value ))
    {
      $wpEntry->set_contact_firstname($value);
    }

    $value = $helper->get_value( '_lastname');
    if( ! empty( $value))
    {
      $wpEntry->set_contact_lastname($value);
    }

    $value = $helper->get_value( '_phone');
    if( ! empty( $value))
    {
      $wpEntry->set_contact_phone($value);
    }

    $value = $helper->get_value( '_website');
    if( ! empty( $value))
    {
      $wpEntry->set_contact_website($value);
    }

    $value = $helper->get_value( '_email');
    if( ! empty( $value))
    {
      $wpEntry->set_contact_email($value);
    }

    $wpEntry->set_location(
      $this->create_location_with_helper($helper, $post));
  }

  public function create_location($entry_post_id, 
                                  $entry_post,
                                  $fill_geo = false)
  {
    $helper = new WPMetaFieldsHelper($entry_post_id);
    $helper->set_prefix($this->get_type()->get_id());
    $helper->set_force_db(true);
    $helper->add_field('_kvm_id');
    $helper->add_field('_kvm_log');
    $helper->add_field('_kvm_do_not_upload');
    $helper->add_field('_type');
    $helper->add_field('_firstname');
    $helper->add_field('_lastname');
    $helper->add_field('_phone');
    $helper->add_field('_website');
    $helper->add_field('_email');
    $helper->add_field('_address');
    $helper->add_field('_zipcode');
    $helper->add_field('_city');
    $helper->add_field('_lat');
    $helper->add_field('_lng');
    return $this->create_location_with_helper($helper, 
                                       $entry_post,
                                       $fill_geo);
  }

  private function create_location_with_helper($helper, 
                                               $post,
                                               $fill_geo = false)
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

    $value = $helper->get_value( '_address');
    if( ! empty( $value))
    {
      $wpLocHelper = new WPLocationHelper();
      $wpLocHelper->set_address($wpLocation, $value);
    }

    $value = $helper->get_value( '_zipcode');
    if( ! empty( $value))
    {
      $wpLocation->set_zip($value);
    }

    $value = $helper->get_value( '_city');
    if( ! empty($value))
    {
      $wpLocation->set_city($value);
    }

    if($fill_geo)
    {
      $value = $helper->get_value( '_lat');
      if( ! empty($value))
      {
        $wpLocation->set_lat($value);
      }

      $value = $helper->get_value( '_lng');
      if( ! empty($value))
      {
        $wpLocation->set_lon($value);
      }
    }
    return $wpLocation;
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

    if(!empty($entry_post->post_excerpt))
    {
      $wpEntry->set_description(
        $entry_post->post_excerpt);
    }
    else if( !empty($entry_post->post_content))
    {
      $wpEntry->set_description(
        wp_trim_excerpt('', $entry_post));
    }
    else
    {
      $wpEntry->set_description('');
    }

    $wpEntry->set_origin_url(get_permalink($entry_post));

    if(has_post_thumbnail($entry_post))
    {
      $wpEntry->set_image_url( get_the_post_thumbnail_url($entry_post) );
      $wpEntry->set_image_link_url( get_permalink($entry_post) );
    }

    $posttags = get_the_tags($entry_post->ID );
    if (!empty($posttags)) 
    {
       foreach($posttags as $tag) 
       {
         $wpEntry->add_tag(new WPTag($tag->name, 
                                          $tag->slug));
       }
    }
    $postcats = get_the_category($entry_post->ID );
    if (!empty($postcats)) 
    {
       foreach($postcats as $cat) 
       {
         $wpEntry->add_category(
           new WPCategory($cat->name, $cat->slug));
       }
    }
  }
}
