<?php
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot . '/local/instilled_media_gallery/lib.php');

global $USER, $PAGE;

if (!property_exists($USER, 'instilledaccesskey') || !$USER->instilledaccesskey) {
  $instilled = new \local_instilled_media_gallery\instilled();
  $instilled->authenticate_user();
}

require_login();

$courseid = required_param('courseid', PARAM_INT);

$context = context_course::instance($courseid);
require_capability('local/instilled_media_gallery:view', $context);

$course = get_course($courseid);

$PAGE->set_context($context);
$PAGE->set_course($course);
$header = get_string('heading_mediagallery', 'local_instilled_media_gallery');

$PAGE->set_url('/local/instilled_media_gallery/index.php', array('courseid' => $courseid));
$PAGE->set_pagetype('instilled_media_gallery-index');
$PAGE->set_pagelayout('standard');
$PAGE->set_title($header);
$PAGE->set_heading($header);

$page_class = 'instilled-media-gallery-body';
$PAGE->add_body_class($page_class);

echo $OUTPUT->header();
$tenant_url = get_config('local_instilled_media_gallery', 'tenanturl');
$default_container = get_config('local_instilled_media_gallery', 'defaultcontainer');

// Request the launch content with an iframe tag.
$attr = array(
  'id' => 'instilled-media-gallery-iframe',
  'height' => '700px',
  'width' => '100%',
  'allowfullscreen' => 'true',
  'src' => $tenant_url.'/moodle/media-gallery?containerId='.$default_container.'&username=' . $USER->username .'&accessKey='. $USER->instilledaccesskey,
  'allow' => 'autoplay *; fullscreen *; encrypted-media *; camera *; microphone *;',
  'style' => 'border: 1px solid #d0d0d0;'
);
echo html_writer::tag('iframe', '', $attr);

echo $OUTPUT->footer();
