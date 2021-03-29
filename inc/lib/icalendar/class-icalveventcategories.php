<?php

/*
 * Parsing ICal Categories
 * @author     Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class ICalVEventCategories
{
  private $logger;
  private $vLine;

  private $categories = array();

  public function __construct($logger, $vLine)
  {
    $this->logger = $logger;
    $this->vLine = $vLine;
  }

  public function get_logger()
  {
    return $this->logger;
  }

  public function log($log)
  {
    $this->get_logger()->add_log($log);
  }

  public function getVLine()
  {
    return $this->vLine;
  }

  private function addCategory($cat)
  {
    array_push($this->categories, $cat);
  }

  public function getCategories()
  {
    return $this->categories;
  }

  public function parse()
  {
    $vLine = $this->getVLine();
    
    $value = $vLine->get_value();
    $cats = explode(',', $value );
    foreach($cats as $cat)
    {
      if(!empty($cat))
      {
        $this->addCategory($cat);
      }
    }
  }

}
