<?php
defined('MOODLE_INTERNAL') || die();
$plugin->version  = 2020090104;
$plugin->requires = 2019111800;
$plugin->cron     = 4 * 3600;
$plugin->component = 'mod_instilledvideo';
$plugin->dependencies = array();
$plugin->release  = '0.0.1';
// MATURITY_ALPHA, MATURITY_BETA, MATURITY_RC, MATURITY_STABLE.
$plugin->maturity = MATURITY_BETA;
$plugin->dependencies = array(
  'local_instilled_media_gallery' => 2020090101,
);