<?php

class KVMEntry
{
  private $_current_module;
  private $_body = array();

  public function __construct($current_module, 
                              $body = array())
  {
    $this->_current_module = $current_module;
    $this->_body = $body;
    if(empty($this->_body['links']))
    {
      if(array_key_exists('custom', 
                          $body))
      {
        $this->_body['links'] = $body['custom'];
      }
    }
  }

  public function get_current_module()
  {
    return $this->_current_module;
  }

  public function get_body()
  {
    return $this->_body;
  }

  public function set_version($version)
  {
    $this->_body['version'] = intval($version);
  }

  public function get_version()
  {
    return intval($this->_body['version']);
  }

  public function get_id()
  {
    return $this->_body['id'];
  }

  public function create_organisation()
  {
    $wpOrganisation = new WPOrganisation();
    $wpOrganisation->set_id( $this->_body['id'] );
    $wpOrganisation->set_name( $this->_body['title'] );
    $wpOrganisation->set_description( $this->_body['description'] );

    $wpLocHelper = new WPLocationHelper();
    $wpLocation = new WPLocation();
    $wpLocation->set_name( $this->_body['title']);
    if(!empty($this->_body['street']))
    {
      $wpLocHelper->set_address($wpLocation, $this->_body['street'] );
    }
    if(!empty($this->_body['zip']))
    {
      $wpLocation->set_zip( $this->_body['zip'] );
    }
    if(!empty($this->_body['city']))
    {
      $wpLocation->set_city( $this->_body['city'] );
    }
    $wpLocation->set_lon( $this->_body['lng'] );
    $wpLocation->set_lat( $this->_body['lat'] );

    $wpOrganisation->set_location($wpLocation);

    if(!empty($this->_body['image_url']))
    {
      $wpOrganisation->set_image_url( $this->_body['image_url']);
      if(!empty($this->_body['image_link_url']))
      {
        $wpOrganisation->set_image_link_url( $this->_body['image_link_url']);
      }
    }

    if(!empty($this->_body['homepage']))
    {
      $wpLink = new WPLink('contact_website', 
                           $this->_body['homepage']); 
      $wpOrganisation->add_link($wpLink);
    }
    $wpOrganisation->set_links($this->get_wp_links());


    if(!empty($this->_body['tags']))
    {
      foreach($this->_body['tags'] as $tag)
      {
        $wpTag = new WPTag($tag, $tag);
        $wpOrganisation->add_tag($wpTag);
      }
    }

    if( !empty( $this->_body['categories'] ))
    {
      foreach( $this->_body['categories'] as $cat)
      {
        if( $cat == '77b3c33a92554bcf8e8c2c86cedd6f6f' )
        {
          $wpOrganisation->set_type_type_id(WPEntryTypeType::COMPANY);
          break;
        }
        if( $cat == '2cd00bebec0c48ba9db761da48678134' )
        {
          $wpOrganisation->set_type_type_id(WPEntryTypeType::INITIATIVE);
          break;
        }
      }
    }

    // TODO: Fill elements füther

    if(!empty($this->_body['version']))
    {
      $wpOrganisation->set_kvm_version( $this->_body['version'] );
    }
    return $wpOrganisation;
  }

  public function fill_entry($wpEntry)
  {
    $this->_body['title'] = $wpEntry->get_name();
    $this->_body['description'] = $wpEntry->get_description();
    $this->_body['telephone'] = $wpEntry->get_contact_phone();
    $this->_body['email'] = $wpEntry->get_contact_email();
    $this->_body['homepage'] = $wpEntry->get_contact_website();

    if(!empty( $wpEntry->get_image_url()))
    {
      $this->_body['image_url'] = $wpEntry->get_image_url();
      $this->_body['image_link_url'] = $wpEntry->get_image_link_url();
    }

    if( WPEntryTypeType::COMPANY === $wpEntry->get_type_type_id())
    {
      $this->_body['categories'] = 
        array('77b3c33a92554bcf8e8c2c86cedd6f6f');
    }
    else
    {
      $this->_body['categories'] = 
        array('2cd00bebec0c48ba9db761da48678134');
    }

    $newlinks = array();
    $existinglinks = $this->get_wp_links();
    foreach($existinglinks as $existingLink)
    {
      $link_to_add = $existingLink;
      //$this->_body['description'] .= 'ADD ELINK=' . $link_to_add->get_url();
      array_push($newlinks, $link_to_add);
    }

    foreach($wpEntry->get_links() as $wpLink)
    {
      $link_to_add = $wpLink;
      foreach($newlinks as $newlink)
      {
        if($wpLink->equals_by_title($newlink->get_title()))
        {
          $newlink->set_url($wpLink->get_url());
          $link_to_add = null;
          break;
        }

        if($wpLink->equals_by_url($newlink->get_url()))
        {
          $link_to_add = null;
          break;
        }
      }

      if(!empty($link_to_add))
      {
        array_push($newlinks, $link_to_add);
      }


    }
    $new_kvm_links = array();
    foreach($newlinks as $newlink)
    {
      array_push($new_kvm_links, $this->convert_to_kvm_link($newlink));
    }
    
    $this->_body['links'] = $new_kvm_links;

    // In KVM ist everything tags, so we convert
    // categories also to tags.
    $tags = array();
    foreach($wpEntry->get_categories() as $wpCat)
    {
      array_push($tags, 
        $this->convert_to_kvm_tag($wpCat->get_name()));
    }

    foreach($wpEntry->get_tags() as $wpTag)
    {
      if( ! in_array(
              $this->convert_to_kvm_tag($wpTag->get_name()), 
              $tags) )
      {
        array_push($tags, 
          $this->convert_to_kvm_tag($wpTag->get_name()));
      }
    }

    $module = $this->get_current_module();

    $fixed_tag = $this->convert_to_kvm_tag(
                 $module->get_kvm_fixed_tag());
    if(!empty($fixed_tag))
    {
      if( ! in_array($fixed_tag, $tags) )
      {
        array_push($tags, $fixed_tag);
      }
    }

    $mc = WPModuleConfiguration::get_instance();
    if($mc->is_module_enabled('wp-project'))
    {
      if($wpEntry->get_type()->get_id() == WPEntryType::PROJECT )
      {
        $fixed_project_tag = $this->convert_to_kvm_tag(
                             $module->get_kvm_fixed_project_tag());
        if(!empty($fixed_project_tag))
        {
          if( ! in_array($fixed_project_tag, $tags) )
          {
            array_push($tags, $fixed_project_tag);
          }
        }
      }
    }

    if(!empty($tags))
    {
      $this->_body['tags'] = $tags;
    }

    $wpLocation = $wpEntry->get_location();
    if(!empty($wpLocation))
    {
      $wpLocH = new WPLocationHelper();
      $address = $wpLocH->get_address($wpLocation);
      
      if(!empty($address))
      {
        $this->_body['street'] = $address;
      }
      
      if(!empty($wpLocation->get_zip()))
      {
        $this->_body['zip'] = $wpLocation->get_zip();
      }
      
      if(!empty($wpLocation->get_city()))
      {
        $this->_body['city'] = $wpLocation->get_city();
      }
      
      if(!empty($wpLocation->get_country_code()))
      {
        $this->_body['country'] = $wpLocation->get_country_code();
      }
      
      if(!empty($wpLocation->get_state()))
      {
        $this->_body['state'] = $wpLocation->get_state();
      }

      if(!empty($wpLocation->get_lat()) 
         && !empty($wpLocation->get_lon()))
      {
        $this->_body['lat'] = doubleval($wpLocation->get_lat());
        $this->_body['lng'] = doubleval($wpLocation->get_lon());
      }
    }
    $this->_body['license'] = 'CC0-1.0';
  }

  private function convert_to_kvm_tag($tag_name)
  {
    if(empty($tag_name))
    {
      return $tag_name;
    }
    return str_replace(' ', '-', $tag_name);
  }

  private function convert_to_kvm_link($wpLink)
  {
    $link = array();
    if(empty($wpLink))
    {
      return null;
    }
    array_push($link, $wpLink->get_url());
    array_push($link, $wpLink->get_title());
    array_push($link, $wpLink->get_description());
    return $link;
  }

  private function get_wp_links()
  {
    $wpLinks = array();
    if(!empty($this->_body['links']))
    {
      foreach($this->_body['links'] as $link)
      {
        $wpLink = new WPLink($link['url'], 
                             $link['url'], 
                             $link['title'], 
                             $link['description']);
        array_push($wpLinks, $wpLink);
      }
    }
    return $wpLinks;
  }

}
