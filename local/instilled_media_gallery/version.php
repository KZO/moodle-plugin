<?php
defined('MOODLE_INTERNAL') || die();

$plugin->component = 'local_instilled_media_gallery';
$plugin->release = 'beta1';
$plugin->version = 2020090101;
$plugin->maturity = MATURITY_BETA;
$plugin->dependencies = array(
  'mod_instilledvideo' => 2020090101,
  'block_instilled_media_gallery' => 2020090101,
);