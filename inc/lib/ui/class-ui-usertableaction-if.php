<?php

/*
 * Does call action if on a UIUserTableAction a action has happened
 * @author     Sjoerd Takken
 * @copyright  No Copyright.
 * @license    GNU/GPLv2, see https://www.gnu.org/licenses/gpl-2.0.html
 */
interface UIUserTableActionIF
{
  public function action($user_id, $user_meta);
}
