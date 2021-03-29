<?php

class UIVACheckbox extends UIViewAdapter
{
  public function show_field()
  {
?><input <?php $this->the_style(); ?> type="checkbox" name="<?php $this->the_id(); ?>" id="<?php $this->the_id(); ?>" value="1" <?php 
disabled($this->is_disabled()); 
echo ' ';
checked($this->get_value(), true); ?>/><?php
  }
}
