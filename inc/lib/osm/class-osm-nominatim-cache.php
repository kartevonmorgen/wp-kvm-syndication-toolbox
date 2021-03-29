<?php

class OsmNominatimCache
{
  private static $instance = null;
  private $_cacheMap = array();

  private function __construct()
  {
  }

  /** 
   * The object is created from within the class itself
   * only if the class has no instance.
   */
  public static function get_instance()
  {
    if (self::$instance == null)
    {
      self::$instance = new OsmNominatimCache();
    }
    return self::$instance;
  }

  public function put($key, $value)
  {
    $this->_cacheMap[$key] = $value;
  }

  public function exists($key)
  {
    return array_key_exists ($key, 
                             $this->_cacheMap);
  }

  public function get($key)
  {
    return $this->_cacheMap[$key];
  }
}
