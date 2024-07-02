<?php

class OsmNominatim
{
  const DEFAULT_URL = 'https://nominatim.openstreetmap.org';
  private $client;

  function __construct()
  {
    $this->client = new WordpressHttpClient();
  }

  public function fill_location($wpLocation)
  {
    if($wpLocation->has_freetextformat_osm())
    {
      return $this->fill_location_freetextformat_osm($wpLocation);
    }
    else
    {
      return $this->fill_location_freetextformat_local($wpLocation);
    }
  }

  private function fill_location_freetextformat_osm($wpLocation)
  {
    $uri = get_option('osm_nominatim_url', self::DEFAULT_URL);
    $uri .= '/search?q=';
    $uri .= trim($wpLocation->get_freetextformat_osm());
    $uri .= '&format=xml&addressdetails=1';
    $uri .= '&countrycodes=' . $wpLocation->get_country_code();
    $wpLocation->set_freetextformat_osm(null);
    return $this->do_request('place', $wpLocation, $uri);
  }

  private function fill_location_freetextformat_local($wpLocation)
  {
    $uri = get_option('osm_nominatim_url', self::DEFAULT_URL);
    $uri .= '/search?q=';

    $addressUri = '';
    if(empty($wpLocation->get_street()))
    {
      return $wpLocation;
    }

    $addressUri .= $wpLocation->get_street();
    $addressUri .=' ';

    if(!empty($wpLocation->get_streetnumber()))
    {
      $addressUri .= $wpLocation->get_streetnumber();
      $addressUri .=' ';
    }

    if(empty($wpLocation->get_zip()) &&
       empty($wpLocation->get_city()))
    {
      return $wpLocation;
    }
      
    if(!empty($wpLocation->get_zip()))
    {
      $addressUri .=',';
      $addressUri .= $wpLocation->get_zip();
      $addressUri .=' ';
    }

    if(!empty($wpLocation->get_city()))
    {
      $addressUri .=',';
      $addressUri .= $wpLocation->get_city();
      $addressUri .=' ';
    }

    if(!empty($wpLocation->get_country_code()))
    {
      $addressUri .=',';
      $addressUri .= $wpLocation->get_country_code();
      $addressUri .=' ';
    }

    $uri .= trim($addressUri);
    $uri .= '&format=xml&addressdetails=1';
    $uri .= '&countrycodes=' . $wpLocation->get_country_code();
    return $this->do_request('place', $wpLocation, $uri);
  }

  public function location_by_geo($lat, $lon)
  {
    $wpLocation = new WPLocation();
    $wpLocation->set_lat($lat);
    $wpLocation->set_lon($lon);

    $uri = get_option('osm_nominatim_url', self::DEFAULT_URL);
    $uri .= '/reverse';
    $uri .= '?format=xml&addressdetails=1';
    $uri .= '&countrycodes=' . $wpLocation->get_country_code();
    $uri .= '&lat=' . $lat;
    $uri .= '&lon=' . $lon;
    return $this->do_request('addressparts', $wpLocation, $uri);
  }

  private function do_request($part, $wpLocation, $uri)
  {
    //echo 'LOAD' . $uri;
    $cache = OsmNominatimCache::get_instance();
    if( $cache->exists($uri))
    {
      $wpLocCache = $cache->get($uri);
      $wpLocation->set_street($wpLocCache->get_street());
      $wpLocation->set_streetnumber(
        $wpLocCache->get_streetnumber());
      $wpLocation->set_zip($wpLocCache->get_zip());
      $wpLocation->set_city($wpLocCache->get_city());
      $wpLocation->set_country_code(
        $wpLocCache->get_country_code());
      $wpLocation->set_lat($wpLocCache->get_lat());
      $wpLocation->set_lon($wpLocCache->get_lon());
      //echo 'GET' . $uri;
      return $wpLocation;
    }
    
    $request = new SimpleRequest('get', $uri, array('referer' => get_bloginfo( 'url' )));
    $response = $this->client->send($request);
    if( $response->getStatusCode() !== 200 )
    {
      return $wpLocation;
    }

    $xmlData = $response->getBody();


    if( empty($xmlData))
    {
      return $wpLocation;
    }

    $xml = simplexml_load_string($xmlData);
    if(empty($xml->children()))
    {
      return $wpLocation;
    }

    foreach($xml->children() as $result_part)
    {
      if($result_part->getName() !== $part)
      {
        continue;
      }

      foreach($result_part->children() as $element_name => $element_value)
      {
        //echo '<p>ELEMENT[' . $element_name . ']: ' . 
        //  $element_value . '</p>';
        switch ($element_name) 
        {
          case 'house_number':
            $wpLocation->set_streetnumber((string)
               $element_value);
            break;
          case 'road':
            $wpLocation->set_street((string)$element_value);
            break;
          case 'town':
          case 'city':
          case 'municipality':
            $wpLocation->set_city((string)$element_value);
            break;
          case 'postcode':
            $wpLocation->set_zip((string)
              $element_value);
            break;
          case 'country_code':
            $wpLocation->set_country_code((string)
              $element_value);
            break;
          case 'natural':
          case 'landuse':
          case 'place':
          case 'man_made':
          case 'aerialway':
          case 'boundary':
          case 'amenity':
          case 'aeroway':
          case 'club':
          case 'craft':
          case 'leisure':
          case 'office':
          case 'shop':
          case 'tourism':
            $wpLocation->set_name((string)
              $element_value);
            break;
        }
      }
    
      if($part === 'place')
      {
        foreach($result_part->Attributes() as $key => $val)
        {
          //echo '<p>ATTRIB[' . $key . ']: ' . $val . '</p>';
          if($key == 'lat')
          {
            $wpLocation->set_lat((string)$val);
          }
          if($key == 'lon')
          {
            $wpLocation->set_lon((string)$val);
          }
        }
      }

      $cache->put($uri, $wpLocation);
      return $wpLocation;
    }
    return $wpLocation;
  }
}
