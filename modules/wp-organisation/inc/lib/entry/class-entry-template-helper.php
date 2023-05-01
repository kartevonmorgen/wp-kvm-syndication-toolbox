<?php

class EntryTemplateHelper extends WPTemplateHelper
{
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
    $this->the_pagination();
  }

  public function show_single()
  {
    if($this->has_current())
    {
      $this->the_content();
      if($this->has_categories())
      {
        $this->the_subtitle('Kategorien');
        $this->the_categories();
      }
      if($this->has_tags())
      {
        $this->the_subtitle('Schlagwörter');
        $this->the_tags();
      }
      if($this->has_events())
      {
        $this->the_subtitle('Veranstaltungen');
        $this->the_events();
        $this->the_linebreak();
      }
      if($this->has_kvm())
      {
        $this->the_subtitle('Karte');
        $this->the_linebreak('5px');
        $this->the_kvm();
        $this->the_linebreak();
      }
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

  

  public function has_kvm()
  {
    $mc = WPModuleConfiguration::get_instance();
    return $mc->is_module_enabled('wp-kvm-interface');
  }

  public function the_kvm($element = 'div', $clazz = null, $style = null)
  {
    $mc = WPModuleConfiguration::get_instance();
    $module = $mc->get_module('wp-kvm-interface');
    if($module == null || !$module->is_module_enabled())
    {
      return;
    }
    $org = $this->current();

    $this->the_begin('div', null, 'text-align:left');
    ?>
      <iframe style="display: inline-block;" src=" https://www.kartevonmorgen.org/m/main?c=<?php echo get_post_meta($org->ID, 'organisation_lat', true); ?>,<?php echo get_post_meta($org->ID, 'organisation_lng', true); ?>&z=18.00&sidebar=hidden&search=%23<?php echo $module->get_kvm_fixed_tag(); ?>" width="100%" height="300">
7           <br />
           <a href="http://kartevonmorgen.org/" target="_blank"     rel="noopener noreferrer">zur karte</a>
       </iframe>
    <?php

    $this->the_end();
  }

  public function has_events()
  {
    $mc = WPModuleConfiguration::get_instance();
    return $mc->is_module_enabled('wp-events-interface');
  }


  public function the_events($bordercolor = '#0F618E', 
                             $format = null, 
                             $format_footer = null)
  {
    $mc = WPModuleConfiguration::get_instance();
    $module = $mc->get_module('wp-events-interface');
    if($module == null || !$module->is_module_enabled())
    {
      return;
    }
    $org = $this->current();
    if($format == null)
    {
      $format = '<div style="margin-top:10px"><div style="text-align:center;border:2px solid '.$bordercolor.';border-radius:8px;float:left;width:200px;padding:5px;margin-right:60px">#_EVENTDATES<br/><i>#_EVENTTIMES</i></div><div style="padding:5px 0px;float:left">#_EVENTLINK{has_location}<br/><i>#_LOCATIONNAME, #_LOCATIONTOWN #_LOCATIONSTATE</i>{/has_location}</div><div style="clear:both"></div></div>';
    }
    if($format_footer == null)
    {
      $format_footer = '<div style="height:10px;clear:both">&nbsp;</div><div style="float:left;width:200px">&nbsp;</div>';
    }
    $module->the_output_list($org->post_author, 
                             $format, 
                             $format_footer);
  }

  public function the_address($element = 'div', $clazz = null)
  {
    $org = $this->current();
    $this->the_element( 'Strasse:', 
                        $element, 
                        $clazz, 
                        'float:left;width:110px');
    $this->the_element( '&nbsp;' . get_post_meta($org->ID, 
                          $this->get_post_type() . '_address', 
                          true), 
                        $element, 
                        $clazz );
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
}
