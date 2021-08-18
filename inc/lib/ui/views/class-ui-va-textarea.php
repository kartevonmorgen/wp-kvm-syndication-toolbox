<?php

class UIVATextarea extends UIViewAdapter
{
  public function __construct($id)
  {
    parent::__construct($id, '460px');
  }

  public function show_field()
  {
?><textarea <?php $this->the_style(); ?> rows="5" name="<?php $this->the_id(); ?>" id="<?php $this->the_id(); ?>" <?php $this->the_disabled(); ?>><?php $this->the_value(); ?></textarea> <?php
  }

}
