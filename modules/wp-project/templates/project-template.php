<?php

global $post;
$helper = new EntryTemplateHelper(WPEntryType::PROJECT);
$helper->load_single($post);
$helper->show_single();
?>
