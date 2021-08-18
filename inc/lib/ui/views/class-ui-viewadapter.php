<?php

abstract class UIViewAdapter
{
  private $_id;
  private $_view;
  private $_title;
  private $_description;
  private $_width;

  public function __construct($id, $width = null)
  {
    $this->_id = $id;
    $this->_width = $width;
  }

  public function show_label()
  {
?><label for="<?php $this->the_id(); ?>"><?php $this->the_title(); ?></label><?php
  }

  public function show_field()
  {
?><input <?php $this->the_style(); ?> type="text" class="regular-text" name="<?php $this->the_id(); ?>" id="<?php $this->the_id(); ?>" value="<?php $this->the_value(); ?>" <?php $this->the_disabled(); ?>/><?php
  }

  public function show_description()
  {
?><span class="description"><em><?php $this->the_description(); ?></em></span><?php
  }

  public function show_newline()
  {
?><br><?php
  }

  public function set_view($view)
  {
    $this->_view = $view;
  }

  protected function get_view()
  {
    return $this->_view;
  }

  public function get_id()
  {
    return $this->_id;
  }

  public function the_id()
  {
    echo $this->get_id();
  }

  public function get_value()
  {
    return $this->get_view()->get_value($this->get_id());
  }

  public function the_value()
  {
    echo $this->get_value();
  }

  public function set_title($title)
  {
    $this->_title = $title;
  }

  protected function get_title()
  {
    if(empty($this->_title))
    {
      return $this->get_view()->get_title($this->get_id());
    }
    return $this->_title;
  }

  public function the_title()
  {
    echo $this->get_title();
  }

  public function set_description($description)
  {
    $this->_description = $description;
  }

  public function has_description()
  {
    return !empty($this->get_description());
  }

  public function get_description()
  {
    if(empty($this->_description))
    {
      return $this->get_view()->get_description($this->get_id());
    }
    return $this->_description;
  }

  public function the_description()
  {
    echo $this->get_description();
  }

  public function get_choices()
  {
    return $this->get_view()->get_choices($this->get_id());
  }

  public function set_disabled($value)
  {
    $this->get_view()->set_disabled($this->get_id(), 
                                    $value);
  }

  public function is_disabled()
  {
    return $this->get_view()->is_disabled($this->get_id());
  }

  public function the_disabled()
  {
    if( $this->is_disabled())
    {
       echo 'disabled="disabled"';
    }
  }

  public function get_backgroundcolor_code()
  {
    return $this->get_view()->get_backgroundcolor_code(
                                $this->get_id());
  }

  public function set_backgroundcolor_id($backgroundcolorid)
  {
    $this->get_view()->set_backgroundcolor_id($this->get_id(), 
                                              $backgroundcolorid);
  }

  public function set_width($width)
  {
    $this->_width = $width;
  }

  public function get_width()
  {
    return $this->_width;
  }

  public function the_style()
  {
    $style = '';
    if(!empty($this->get_backgroundcolor_code()))
    {
      $style .= 'background-color:#' . 
                $this->get_backgroundcolor_code() . ';';
    }
    if(!empty($this->get_width()))
    {
      $style .= 'width:' . 
                $this->get_width() . ';';
    }
    if(empty($style))
    {
      return;
    }
    echo 'style="' . $style . '"';
  }
}
