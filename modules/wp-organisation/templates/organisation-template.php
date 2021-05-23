<?php

global $post;
$helper = new OrganisationTemplateHelper();
$helper->load_single($post);
$helper->show_single();
?>
