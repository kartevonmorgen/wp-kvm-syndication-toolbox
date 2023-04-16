<?php
/**
  * This instance is used to create an Instance of the wished Type,
  * depending which kind of WPEntry it is, WPOrganisation, WPProject
  *
  * @author     Sjoerd Takken 
  * @copyright  No Copyright.
  * @license   	GNU/GPLv2, see http://www.gnu.org/licenses/gpl-2.0.html
  * @link       https://github.com/kartevonmorgen
  */
class WPEntryType
{
  const ORGANISATION = 'organisation'; 
  const PROJECT = 'project'; 

  private $_id;
  private $_title;
  private $_class;

	function __construct($id, $title, $class)
  {
    $this->_id = $id;
    $this->_title = $title;
    $this->_class = $class;
  }

  public function get_id()
  {
    return $this->_id;
  }

  public function get_title()
  {
    return $this->_title;
  }

  public function get_clazz()
  {
    return $this->_class;
  }

  public function __toString()
  {
    return $this->_title;
  }
}
