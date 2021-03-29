<?php

class UIVACombobox extends UIViewAdapter
{
  public function show_field()
  {
?><select name="<?php $this->the_id(); ?>" id="<?php $this->the_id(); ?>"><?php
    foreach($this->get_choices() as $choice)
    {
      echo '<option value="'.$choice->get_id();
      if($choice->get_id() == $this->get_value())
      {
        echo '" selected="selected';
      }
      echo '">'.$choice->get_name().'</option>';
    }
?></select><?php
  }

}
