<?php

/*
 * Does call action if on a UITableAction a action has happened
 * @author     Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
interface UIPostTableActionIF
{
  public function action($post_id, $post);
}
