<?php

/*
 * Parsing ICal Text Type
 * @author     Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
class ICalVEventLocation
{
  private $logger;
  private $vLine;

  private $result;

  public function __construct($logger, $vLine)
  {
    $this->logger = $logger;
    $this->vLine = $vLine;
  }

  private function getVLine()
  {
    return $this->vLine;
  }

  private function get_logger()
  {
    return $this->logger;
  }

  private function setResult($result)
  {
    $this->result = $result;
  }

  public function getResult()
  {
    return $this->result;
  }

  public function parse()
  {
    $vLine = $this->getVLine();
    $value = $vLine->get_value();

    // Remove the "\," because it is removed by wordpress and then we always get trouble with comparing
    $value = str_replace("\,", ",", $value);

    $this->setResult($value);
  }
}

