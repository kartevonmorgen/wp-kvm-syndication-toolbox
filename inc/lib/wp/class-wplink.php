<?php
/**
  * WPLink
  * WPLink contains all the Links with an Id about what it is.
  * 
  * @author     Sjoerd Takken
  * @copyright  No Copyright.
  * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
  */
class WPLink 
{
  private $_id;
  private $_url;
  private $_title;
  private $_description;
  
  public function __construct($id, $url, $title='', $description='') 
  {
		$this->_id = $id;
    $this->_url = $url;
    $this->_title = $title;
    $this->_description = $description;
  }

	public function get_id() 
  {
		return $this->_id;
	}

  public function set_url($url)
  {
    $this->_url = $url;
  }

	public function get_url() 
  {
		return $this->_url;
	}

  public function set_title($title)
  {
    $this->_title = $title;
  }

	public function get_title() 
  {
		return $this->_title;
	}

  public function set_description($description)
  {
    $this->_description = $description;
  }

	public function get_description() 
  {
		return $this->_description;
	}

  public function equals_by_id($id)
  {
    if(trim($this->get_id()) === trim($id))
    {
      return true;
    }

    return false;
  }

  public function equals_by_title($title)
  {
    if(trim($this->get_title()) === trim($title))
    {
      return true;
    }

    return false;
  }

  public function equals_by_url($url)
  {
    $thisurl = $this->get_url();
    $thisurl = parse_url($thisurl, PHP_URL_HOST);
    $thisurl = str_replace('www.', '', $thisurl);

    $thaturl = $url;
    $thaturl = parse_url($thaturl, PHP_URL_HOST);
    $thaturl = str_replace('www.', '', $thaturl);

    if($thisurl === $thaturl)
    {
      return true;
    }

    return false;
  }

  public function to_string()
  {
    return ''. $this->_id.' (' . $this->_title . ' -> ' . $this->_url . ')';
  }
}
