<?php

/**
  * WPEntry
  * 
  * @author     Sjoerd Takken
  * @copyright  No Copyright.
  * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
  */
class WPEntry 
{
  private $_id;
  private $_name;
  private $_kvm_id;
  private $_kvm_version;
  private $_type_id;
  private $_description;
  private $_status;
  private $_contact_firstname;
  private $_contact_lastname;
  private $_contact_phone;
  private $_contact_email;
  private $_image_url;
  private $_image_link_url;

  private $_links = array();
	private $_categories = array();
	private $_tags = array();
	private $_location;
  
  public function __construct() 
  {
  }

	public function set_id( $id ) 
  {
		$this->_id = $id;
	}

	public function get_id() 
  {
		return $this->_id;
	}

	public function set_name( $name ) 
  {
		$this->_name = $name;
	}

	public function get_name() 
  {
		return $this->_name;
	}

	public function set_status( $status ) 
  {
		$this->_status = $status;
	}

	public function get_status() 
  {
		return $this->_status;
	}


	public function set_kvm_id( $kvm_id ) 
  {
		$this->_kvm_id = $kvm_id;
	}

	public function get_kvm_id() 
  {
		return $this->_kvm_id;
	}

	public function set_kvm_version( $kvm_version ) 
  {
		$this->_kvm_version = $kvm_version;
	}

	public function get_kvm_version() 
  {
		return $this->_kvm_version;
	}

	public function set_type_id( $type_id ) 
  {
		$this->_type_id = $type_id;
	}

	public function get_type_id() 
  {
		return $this->_type_id;
  }

  public function set_description( $description ) 
  {
    $this->_description = $description;
  }

  public function get_description() 
  {
    return $this->_description;
  }

	public function set_contact_firstname( $contact_firstname ) 
  {
		$this->_contact_firstname = $contact_firstname;
	}

	public function get_contact_firstname() 
  {
		return $this->_contact_firstname;
	}

	public function set_contact_lastname( $contact_lastname ) 
  {
		$this->_contact_lastname = $contact_lastname;
	}

	public function get_contact_lastname() 
  {
		return $this->_contact_lastname;
	}

	public function set_contact_phone( $contact_phone ) 
  {
		$this->_contact_phone = $contact_phone;
	}

	public function get_contact_phone() 
  {
		return $this->_contact_phone;
	}

	public function set_contact_email( $contact_email ) 
  {
		$this->_contact_email = $contact_email;
	}

	public function get_contact_email() 
  {
		return $this->_contact_email;
	}

	public function set_contact_website( $contact_website ) 
  {
    $link = new WPLink('contact_website', 
                       $contact_website,
                       'Webseite der Organisation');
    $this->add_link($link);
	}

	public function get_contact_website() 
  {
		$link = $this->get_link('contact_website');
    if(empty($link))
    {
      return '';
    }
    return $link->get_url();
	}

  public function set_origin_url( $origin_url )
  {
    $link = new WPLink( 'origin_url', 
                        $origin_url,
                        'Seite auf ' . get_bloginfo( 'name' ));
		$this->add_link($link);
  }
  
	public function get_origin_url() 
  {
		$link = $this->get_link('origin_url');
    if(empty($link))
    {
      return '';
    }
    return $link->get_url();
  }

  public function set_image_url($image_url)
  {
    $this->_image_url = $image_url;
  }

  public function get_image_url()
  {
    return $this->_image_url;
  }

  public function set_image_link_url($image_link_url)
  {
    $this->_image_link_url = $image_link_url;
  }

  public function get_image_link_url()
  {
    return $this->_image_link_url;
  }

  /*
   * Set an array of WPCategory objects
   */
	public function set_categories( $categories ) 
  {
    $this->_categories = $categories;
	}

  /*
   * Add an array of WPCategory objects
   */
	public function add_category( $category ) 
  {
    array_push($this->_categories, $category);
	}

  /*
   * Return an array of WPCategory objects
   */
	public function get_categories() 
  {
		return $this->_categories;
	}

  /*
   * Add an array of WPTag objects
   */
	public function add_tag( $tag ) 
  {
    array_push($this->_tags, $tag);
	}

  /*
   * Set an array of WPTag objects
   */
	public function set_tags( $tags ) 
  {
    $this->_tags = $tags;
	}

  /*
   * Return an array of WPTag objects
   */
	public function get_tags() 
  {
		return $this->_tags;
  }

  /*
   * Add an array of WPLink objects
   */
	public function add_link( $link ) 
  {
    array_push($this->_links, $link);
	}

  /*
   * Set an array of WPLink objects
   */
	public function set_links( $links ) 
  {
    $this->_links = $links;
	}

  /*
   * Return an array of WPLink objects
   */
	public function get_links() 
  {
		return $this->_links;
  }

  public function get_link_by_url($url)
  {
    foreach($this->get_links() as $link)
    {
      if($link->equals_by_url($url))
      {
        return $link;
      }
    }
    return $link;
  }

  public function get_link($id)
  {
    if(empty($id))
    {
      return null;
    }
    foreach($this->get_links() as $link)
    {
      if($link->get_id() === $id)
      {
        return $link;
      }
    }
    return $link;
  }

	public function set_location( $location ) 
  {
		$this->_location = $location;
	}

	public function get_location() 
  {
		return $this->_location;
	}


  private function add_line($caption, $value)
  {
    return ''. $caption . ': ' . $value . PHP_EOL;
  }
    
  public function to_text()
  {
    $result = '';
    $result .= $this->add_line('id', $this->get_id());
    $result .= $this->add_line('name', $this->get_name());
    $result .= $this->add_line('kvm_id', $this->get_kvm_id());
    $result .= $this->add_line('description', $this->get_description());
    $location = $this->get_location();
    if(!empty ( $location ))
    {
      $result .= $this->add_line('location_name', $location->get_name());
      $result .= $this->add_line('location_street', $location->get_street());
      $result .= $this->add_line('location_streetnumber', $location->get_streetnumber());
      $result .= $this->add_line('location_zip', $location->get_zip());
      $result .= $this->add_line('location_city', $location->get_city());
      $result .= $this->add_line('location_state', $location->get_state());
      $result .= $this->add_line('location_country_code', $location->get_country_code());
      $result .= $this->add_line('location_lon', $location->get_lon());
      $result .= $this->add_line('location_lat', $location->get_lat());
    }
    return $result;
  }

  public function to_string()
  {
    return '' . 
           $this->get_id() .
           ' (' . 
           $this->get_name() . 
           ' ' .
           $this->get_description() .
           ' )';
  }
}
