<?php

global $post;
$helper = new EntryTemplateHelper(WPEntryType::ORGANISATION);
$helper->load_single($post);
$helper->show_single();
?>
