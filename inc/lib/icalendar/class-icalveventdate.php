<?php

/*
 * Parsing ICal Dates
 * @author     Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class ICalVEventDate
{
  private $logger;
  private $vLine;

  private $isDate;
  private $timestamps = array();
  private $dateHelper;

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

  private function addTimestamp($timestamp)
  {
    array_push( $this->timestamps, $timestamp );
  }

  public function getTimestamps()
  {
    return $this->timestamps;
  }

  public function getTimestamp()
  {
    return reset($this->timestamps);
  }

  private function setDate($isDate)
  {
    $this->isDate = $isDate;
  }

  public function isDate()
  {
    return $this->isDate;
  }

  public function parse()
  {
    $timezone = wp_timezone();
    $dateHelper = new ICalDateHelper();
    $vLine = $this->getVLine();

    $this->setDate(false);
    $value = $vLine->get_parameter('VALUE');
    if($value == 'DATE')
    {
      $this->setDate(true);

    }
    $value = $vLine->get_value();
    $dateValues = explode(',', $value);
    foreach($dateValues as $dateValue)
    {
      $ts = $dateHelper->fromiCaltoUnixDateTime($dateValue, $timezone);
      $this->addTimestamp($ts);
    }
  }

}
