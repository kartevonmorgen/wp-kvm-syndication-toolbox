<?php

class ICalVLine
{
  private $logger;
  private $line;
  private $su;

  private $id;
  private $parameters = array();
  private $value;

  function __construct($logger, $line)
  {
    $this->logger = $logger;
    $this->line = $line;
    $this->su = new PHPStringUtil();
  }

  private function get_logger()
  {
    return $this->logger;
  }

  private function log($log)
  {
    $this->get_logger()->add_log($log);
  }

  public function getStringUtil()
  {
    return $this->su;
  }

  public function getLine()
  {
    return $this->line;
  }

  public function parse()
  {
    $su = $this->getStringUtil();
    $line = $this->getLine();
    $keyPart = $line;

    $pos = $su->findPositionWithoutQuotes($line, ':');
    if($pos !== false)
    {
      $keyPart = substr($line, 0, $pos);
      $valuePart = substr($line, $pos + 1);
      $this->set_value($valuePart);
    }

    $id = $keyPart;
    $pos = $su->findPositionWithoutQuotes($keyPart, ';');
    if($pos !== false)
    {
      $id = substr($keyPart, 0, $pos);
      $params = substr($keyPart, $pos + 1);
      $this->set_parameters( $su->findParameters($params));
      //echo 'PARAMS: ID(' . $id . ') ' . $params;
      //var_dump( $this->get_parameters());
      //die;
    }
    $this->set_id($id);
    //$this->log('ID=' . $id . ', VALUE=' . $valuePart);
    //echo 'ID=' . $id . ', VALUE=' . $valuePart;
    //die;
  }

  private function set_id($id)
  {
    $this->id = $id;
  }

  public function get_id()
  {
    return $this->id;
  }

  private function set_parameters($parameters)
  {
    $this->parameters = $parameters;
  }

  public function get_parameters()
  {
    return $this->parameters;
  }

  public function has_parameter($key)
  {
    return array_key_exists($key, $this->parameters);
  }

  public function get_parameter($key)
  {
    if($this->has_parameter($key))
    {
      return $this->parameters[$key];
    }
    return null;
  }

  private function set_value($value)
  {
    $this->value = $value;
  }

  public function get_value()
  {
    return $this->value;
  }

}
