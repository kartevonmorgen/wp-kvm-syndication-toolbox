<?php

class InUserRegisterView extends UIView
{
  const DS_BGCOLOR = 'yellow';

  private $_urfields = null;

  private function get_urfields()
  {
    if($this->_urfields !== null)
    {
      return $this->_urfields;
    }
    $fields = array();
    $args = array(
       'numberposts' => 20,
       'post_type'   => 'urpost');
    $urposts = get_posts($args);
    foreach($urposts as $urpost)
    {
      $field = new WPUserRegisterField($urpost);
      array_push($fields, $field);
    }
    
    usort($fields, function($f1, $f2) 
    {
      return $f1->get_position() - $f2->get_position();
    });

    $this->_urfields = $fields;
    return $this->_urfields;
  }

  public function init()
  {
    $fields = $this->get_urfields();
    if(empty($fields))
    {
      return;
    }
    foreach($fields as $field)
    {
      if($field->is_field())
      {
        $v = $this->add_va($field->get_id());
        $v->set_backgroundcolor_id($field->get_bgcolor_id());
        $v->set_title($field->get_title());
        if($field->has_description())
        {
          $v->set_description($field->get_description());
        }
      }
    }
  }

  public function show()
  {
    $fields = $this->get_urfields();
    if(empty($fields))
    {
      return;
    }
    foreach($fields as $field)
    {
      if($field->is_text())
      {
?><p>&nbsp;</p>
<p><b><?php echo $field->get_title(); ?></b></p>
<hr>
<?php    if($field->has_description()) 
        {?>
<p>&nbsp;</p>
<?php     echo $field->get_description(); 
        }?>
<p>&nbsp;</p>
<p>&nbsp;</p>
<?php
      }
      else
      {
        $va = $this->get_viewadapter($field->get_id());
?><p><?php
        $va->show_label();
        $va->show_newline();
        $va->show_field();
        if($va->has_description()) 
        { 
          $va->show_newline();
          $va->show_description();
          $va->show_newline();
          $va->show_newline();
        } 
      }
?></p>
<?php
    }
  }
}
