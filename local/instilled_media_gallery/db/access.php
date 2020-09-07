<?php
$capabilities = array(
  'local/instilled_media_gallery:view' => array(
    'captype' => 'read',
    'contextlevel' => CONTEXT_COURSE,
    'archetypes' => array(
      'student' => CAP_PROHIBIT,
      'editingteacher' => CAP_ALLOW,
      'teacher' => CAP_ALLOW,
      'manager' => CAP_ALLOW
    )
  ),
);
