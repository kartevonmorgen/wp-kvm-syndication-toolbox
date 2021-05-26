<?php

$page = 0;
if(array_key_exists('pno', $_GET))
{
  $page = $_GET['pno'];
}

$helper = new OrganisationTemplateHelper();
$helper->load($page);
$helper->show();
?>
