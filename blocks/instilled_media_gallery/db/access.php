<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = array(
  'block/instilled_media_gallery:myaddinstance' => array(
    'captype' => 'write',
    'contextlevel' => CONTEXT_SYSTEM,
    'archetypes' => array(
      'user' => CAP_PROHIBIT
    ),

    'clonepermissionsfrom' => 'moodle/my:manageblocks'
  ),

  'block/instilled_media_gallery:addinstance' => array(
    'riskbitmask' => RISK_SPAM | RISK_XSS,
    'captype' => 'write',
    'contextlevel' => CONTEXT_BLOCK,
    'archetypes' => array(
      'editingteacher' => CAP_ALLOW,
      'manager' => CAP_ALLOW
    ),

    'clonepermissionsfrom' => 'moodle/site:manageblocks'
  ),
);
