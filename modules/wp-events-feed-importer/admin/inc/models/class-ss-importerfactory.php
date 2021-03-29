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
final class SSImporterFactory
{
  private static $instance = null;

  private $_importtypes;

  private function __construct() 
  {
    $this->_importtypes = array();
    array_push( $this->_importtypes, 
                new SSImportType('ical', 
                                 'ICal Feed',
                                 'SSICalImport'));

    array_push( $this->_importtypes, 
                new SSImportType('ess', 
                                 'ESS Feed', 
                                 'SSESSImport'));
  }

  /** 
   * The object is created from within the class itself
   * only if the class has no instance.
   */
  public static function get_instance()
  {
    if (self::$instance == null)
    {
      self::$instance = new SSImporterFactory();
    }
    return self::$instance;
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
    return new $class($feed);
  }

  public function get_importtypes()
  {
    return $this->_importtypes;
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
