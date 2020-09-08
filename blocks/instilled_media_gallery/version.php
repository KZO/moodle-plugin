<?php
defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2020090101;
$plugin->component = 'block_instilled_media_gallery';
$plugin->dependencies = array(
  'local_instilled_media_gallery' => 2020090101,
  'mod_instilledvideo' => 2020090101,
);