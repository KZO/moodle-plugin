<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Description.
 *
 * @since Moodle 3.7
 * @package local_instilled_media_gallery
 * @copyright  2020 Instilled <support@instilled.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot . '/local/instilled_media_gallery/locallib.php');

global $USER, $PAGE;

require_login();

$courseid = required_param('courseid', PARAM_INT);
$context = context_course::instance($courseid);
require_capability('local/instilled_media_gallery:view', $context);

$instilled = new \local_instilled_media_gallery\instilled();
$instilledaccesskey = $instilled->authenticate_user($context);

$course = get_course($courseid);

$PAGE->set_context($context);
$PAGE->set_course($course);
$header = get_string('heading_mediagallery', 'local_instilled_media_gallery');

$PAGE->set_url('/local/instilled_media_gallery/index.php', array('courseid' => $courseid));
$PAGE->set_pagetype('instilled_media_gallery-index');
$PAGE->set_pagelayout('standard');
$PAGE->set_title($header);
$PAGE->set_heading($header);

echo $OUTPUT->header();
$tenanturl = get_config('local_instilled_media_gallery', 'tenanturl');
$defaultcontainer = get_config('local_instilled_media_gallery', 'defaultcontainer');

// Request the launch content with an iframe tag.
$attr = array(
    'id' => 'instilled-media-gallery-iframe',
    'height' => '700px',
    'width' => '100%',
    'allowfullscreen' => 'true',
    'src' => $tenanturl.'/moodle/media-gallery?containerId='.$defaultcontainer.'&username=' . $USER->username .'&accessKey='. $instilledaccesskey,
    'allow' => 'autoplay *; fullscreen *; encrypted-media *; camera *; microphone *;',
    'style' => 'border: 1px solid #d0d0d0;'
);
echo html_writer::tag('iframe', '', $attr);

echo $OUTPUT->footer();
