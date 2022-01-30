<?php
/**
  * This instance is used to create an importer,
  * depending on the Feed type
  *
  * @author     Sjoerd Takken 
  * @copyright  No Copyright.
  * @license   	GNU/GPLv2, see http://www.gnu.org/licenses/gpl-2.0.html
  * @link       https://github.com/kartevonmorgen
  */
final class SSImporterFactory extends WPAbstractModuleProvider 
{
  private $_importtypes = null;

  public function get_importtypes()
  {
    if(!empty($this->_importtypes))
    {
      return $this->_importtypes;
    }

    $this->_importtypes = array();
    array_push( $this->_importtypes, 
                new SSImportType('ical', 
                                 'ICal Feed',
                                 'SSICalImport'));

    array_push( $this->_importtypes, 
                new SSImportType('ess', 
                                 'ESS Feed', 
                                 'SSESSImport'));

    array_push( $this->_importtypes, 
                new SSImportType('mobilizon', 
                                 'Mobilizon', 
                                 'SSMobilizonImport'));
    return $this->_importtypes;
  }

  public function create_importer($feed)
  {
    if(empty($feed))
    {
      return null;
    }
    $feed_type = get_post_meta($feed->ID,'ss_feedurltype',1);
    $importtype = $this->get_importtype($feed_type);
    $class = $importtype->get_clazz();
    return new $class($this->get_current_module(), $feed);
  }

  public function get_importtype($id)
  {
    if(empty($id))
    { 
      // If empty, we just return the first one
      // to backwards compatible
      return $this->get_defaultimporttype();
    }

    foreach($this->get_importtypes() as $type)
    {
      if($type->get_id() == $id)
      {
        return $type;
      }
    }

    // If not found, we just return the first one
    // to backwards compatible
    return $this->get_defaultimporttype();
  }

  public function get_defaultimporttype()
  {
    return reset($this->get_importtypes());
  }

  public function is_valid_feedtype($feed_type)
  {
    return !empty($this->get_importtype($feed_type));
  }

}
