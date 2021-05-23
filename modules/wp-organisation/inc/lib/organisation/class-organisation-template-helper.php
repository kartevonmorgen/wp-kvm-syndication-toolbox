<?php

class OrganisationTemplateHelper
{
  private $organisations = array();
  private $pointer = -1;
  private $parent_elements = array();

  public function load()
  {
    $this->organisations = get_posts(array('post_type' => 'organisation'));
    $this->pointer = -1;
  }

  public function load_single($post)
  {
    $this->organisations = array( get_post($post));
    $this->pointer = 0;
  }

  public function show()
  {
    while($this->has_next())
    {
      $this->next();
      $this->the_linebreak('span');
      $this->the_title();
      $this->the_excerpt();
      $this->the_permalink();
    }

  }

  public function show_single()
  {
    if($this->has_current())
    {
      $this->the_content();
      $this->the_subtitle('Kategorien');
      $this->the_categories();
      $this->the_subtitle('Schlagwörter');
      $this->the_tags();
      // -- Here you can show events --
      $this->the_subtitle('Kontaktdaten');
      $this->the_linebreak();
      $this->the_address();
      $this->the_city();
      $this->the_linebreak();
      $this->the_phone();
      $this->the_linebreak();
      $this->the_email();
      $this->the_website();
    }
  }


  public function the_linebreak($element = 'div', $clazz = null, $style = null)
  {
    $this->the_element( '&nbsp;', $element, $clazz, $style );
  }

  public function the_title($element = 'h3', $clazz = null, $style = null, $stylea = null)
  {
    $org = $this->current();
    $stylea_css = '';
    if(!empty($stylea))
    {
      $stylea_css = ' style="' . $stylea . '"';
    }
    $value = '<a href="'.get_the_permalink($org).'"' . $stylea_css . '>';
    $value .= get_the_title($org);
    $value .= '</a>';
    $this->the_element( $value, $element, $clazz, $style );
  }

  public function the_subtitle($value, $element = 'h4', $clazz = null, $style = null)
  {
    $this->the_element( $value, $element, $clazz, $style );
  }

  public function the_excerpt($element = 'p', $clazz = null, $style = null)
  {
    $org = $this->current();
    $this->the_element( get_the_excerpt($org), $element, $clazz, $style );
  }

  public function the_permalink($text = '.. weitere Informationen ..', 
                                $element = 'p', 
                                $clazz = null, 
                                $style = null,
                                $stylea = null)
  {
    $org = $this->current();
    $stylea_css = '';
    if(!empty($stylea))
    {
      $stylea_css = ' style="' . $stylea . '"';
    }
    $this->the_element( '<a href="' . 
                           get_the_permalink($org) . 
                           '"' .
                           $stylea_css . '>' . $text. '</a>', 
                         $element, $clazz, $style );
  }
  
  public function the_content($element = 'div', $clazz = null, $style = null)
  {
    $org = $this->current();
    $this->the_element( get_the_content(null, false, $org), $element, $clazz, $style );
  }

  public function the_categories($element = 'p', $clazz = null, $style = null)
  {
    $org = $this->current();
    $this->the_element( get_the_category_list(', ','', $org->ID), $element, $clazz, $style );
  }

  public function the_tags($element = 'p', $clazz = null, $style = null)
  {
    $org = $this->current();
    $this->the_element( '&nbsp;' . get_the_tag_list('', ', ','', $org->ID), $element, $clazz, $style );
  }

  public function the_address($element = 'div', $clazz = null)
  {
    $org = $this->current();
    $this->the_element( 'Strasse:', 
                        $element, 
                        $clazz, 
                        'float:left;width:110px');
    $this->the_element( '&nbsp;' . get_post_meta($org->ID, 'organisation_address', true), $element, $clazz );
  }

  public function the_city($element = 'div', $clazz = null)
  {
    $org = $this->current();
    $this->the_element( 'Ort:', 
                        $element, 
                        $clazz, 
                        'float:left;width:110px');
    $this->the_element( '&nbsp;' . get_post_meta($org->ID, 'organisation_city', true), $element, $clazz );
  }

  public function the_phone($element = 'div', $clazz = null)
  {
    $org = $this->current();
    $this->the_element( 'Telefon:', 
                        $element, 
                        $clazz, 
                        'float:left;width:110px');
    $this->the_element( '&nbsp;' . get_post_meta($org->ID, 'organisation_phone', true), $element, $clazz );
  }

  public function the_email($element = 'div', $clazz = null)
  {
    $org = $this->current();
    $this->the_element( 'Email:', 
                        $element, 
                        $clazz, 
                        'float:left;width:110px');
    $this->the_element( '&nbsp;' . get_post_meta($org->ID, 'organisation_email', true), $element, $clazz );
  }

  public function the_website($element = 'div', $clazz = null)
  {
    $org = $this->current();
    $this->the_element( 'Webseite:', 
                        $element, 
                        $clazz, 
                        'float:left;width:110px');
    $this->the_element( '&nbsp;' . get_post_meta($org->ID, 'organisation_website', true), $element, $clazz );
  }

  public function the_element($value, 
                              $element = 'p', 
                              $clazz = null,
                              $style = null)
  {
    $this->the_begin($element, $clazz, $style);
    echo '' . $value;
    $this->the_end();
  }

  public function the_begin($element = 'p', $clazz = null, $style = null)
  {
    $clazz_css = '';
    $style_css = '';
    array_push($this->parent_elements, $element);

    if(!empty($clazz))
    {
      $clazz_css = ' class="' . $clazz . '"';
    }

    if(!empty($style))
    {
      $style_css = ' style="' . $style . '"';
    }

    if(count($this->parent_elements) > 1)
    {
      echo '  ';
    }
    
    echo "<" . $element . $clazz_css . $style_css . ">";
    if($element == 'p')
    {
      echo "\n";
    }
  }

  public function the_end()
  {
    $element = array_pop($this->parent_elements);
    echo '</' . $element . '>';
    echo "\n";
  }

  public function next()
  {
    $this->pointer = $this->pointer + 1;
    return $this->current();
  }

  public function has_next()
  {
    return ($this->pointer + 1 < count($this->organisations));
  }

  public function current()
  {
    return $this->organisations[$this->pointer];
  }

  public function has_current()
  {
    return ($this->pointer < count($this->organisations));
  }

}
