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
final class WPEntryTypeFactory extends WPAbstractModuleProvider 
{
  private $_types = null;

  public function get_types()
  {
    if(!empty($this->_types))
    {
      return $this->_types;
    }

    $this->_types = array();
    array_push( $this->_types, 
                new WPEntryType(WPEntryType::ORGANISATION, 
                                'Organisation',
                                'WPOrganisation'));

    array_push( $this->_types, 
                new WPEntryType(WPEntryType::PROJECT, 
                                'Projekt', 
                                'WPProject'));

    return $this->_types;
  }

  public function create_wp_entry($id)
  {
    if(empty($id))
    {
      return null;
    }
    $type = $this->get_type($id);
    $class = $importtype->get_clazz();
    return new $class();
  }

  public function get_type($id)
  {
    if(empty($id))
    { 
      // If empty, we just return the first one
      // to backwards compatible
      return $this->get_defaulttype();
    }

    foreach($this->get_types() as $type)
    {
      if($type->get_id() == $id)
      {
        return $type;
      }
    }

    // If not found, we just return the first one
    // to backwards compatible
    return $this->get_defaulttype();
  }

  public function get_defaulttype()
  {
    return reset($this->get_types());
  }
}
