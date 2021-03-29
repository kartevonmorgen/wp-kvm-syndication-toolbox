<?php

/**
  * WPLocation
  * 
  * @author     Sjoerd Takken
  * @copyright  No Copyright.
  * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
  */
class WPLocation 
{
  private $_name;
  private $_street;
  private $_streetnumber;
  private $_city;
  private $_zip;
  private $_state;
  private $_country_code;
  private $_website;
	private $_phone;
  private $_lon;
	private $_lat;

	private $_freetextformat_osm = null;
  
  public function __construct() 
  {
  }

  public function set_freetextformat_osm($freetextformat)
  {
    $this->_freetextformat_osm = $freetextformat;
  }

  public function get_freetextformat_osm()
  {
    return $this->_freetextformat_osm;
  }

  public function has_freetextformat_osm()
  {
    return !empty($this->_freetextformat_osm);
  }

	public function set_name( $name ) 
  {
		$this->_name = $name;
	}

	public function get_name() 
  {
		return $this->_name;
	}

  public function set_street( $street ) 
  {
    $this->_street = $street;
  }

  public function get_street() 
  {
    return $this->_street;
  }

  public function set_streetnumber( $streetnumber ) 
  {
    $this->_streetnumber = $streetnumber;
  }

  public function get_streetnumber() 
  {
    return $this->_streetnumber;
  }

  public function set_zip( $zip ) 
  {
    $this->_zip = $zip;
  }

  public function get_zip() 
  {
    return $this->_zip;
  }

  public function set_city( $city ) 
  {
    $this->_city = $city;
  }

  public function get_city() 
  {
    return $this->_city;
  }

  public function set_state( $state ) 
  {
    $this->_state = $state;
  }

  public function get_state() 
  {
    return $this->_state;
  }

  public function set_country_code( $country_code ) 
  {
    $this->_country_code = $country_code;
  }

  public function get_country_code() 
  {
	  if ( empty( $this->_country_code )) 
    {
      return 'DE';
    }
    return $this->_country_code;
  }

  public function set_website( $website ) 
  {
    $this->_website = $website;
  }

  public function get_website() 
  {
    return $this->_website;
  }

	public function set_phone( $phone ) 
  {
		$this->_phone = $phone;
	}

	public function get_phone() 
  {
		return $this->_phone;
	}
    
	public function set_lon( $lon ) 
  {
		$this->_lon = $lon;
	}

	public function get_lon() 
  {
		return $this->_lon;
	}
    
	public function set_lat( $lat ) 
  {
		$this->_lat = $lat;
	}

	public function get_lat() 
  {
		return $this->_lat;
	}

  public function equals($wpLocation)
  {
    if(empty($wpLocation))
    {
      return false;
    }

    if($this->get_lon() !== $wpLocation->get_lon())
    {
      return false;
    }

    if($this->get_lat() !== $wpLocation->get_lat())
    {
      return false;
    }
    return true;
  }

  public function to_string()
  {
    if($this->has_freetextformat_osm())
    {
      return 'FTT-OSM(' . $this->get_freetextformat_osm() . ')';
    }
    return '' . 
           $this->get_name() .
           ' (' . 
           $this->get_street() . 
           ' ' .
           $this->get_streetnumber() .
           ' ' .
           $this->get_zip() .
           ' ' .
           $this->get_city() .
           ' (' .
           $this->get_lat() .
           ', ' .
           $this->get_lon() .
           ' ))';
  }
}
