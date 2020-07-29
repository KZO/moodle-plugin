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
 * Instilled Video activity module
 *
 * @package    mod
 * @subpackage instilledvideo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

// Course_module ID, or
$id = optional_param('id', 0, PARAM_INT);

// ... module instance id.
$i  = optional_param('i', 0, PARAM_INT);

if ($id) {
  $cm             = get_coursemodule_from_id('instilledvideo', $id, 0, false, MUST_EXIST);
  $course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
  $moduleinstance = $DB->get_record('instilledvideo', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($i) {
  $moduleinstance = $DB->get_record('instilledvideo', array('id' => $n), '*', MUST_EXIST);
  $course         = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
  $cm             = get_coursemodule_from_instance('instilledvideo', $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
  print_error(get_string('missingidandcmid', 'mod_instilledvideo'));
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

$event = \mod_instilledvideo\event\course_module_viewed::create(array(
  'objectid' => $moduleinstance->id,
  'context' => $modulecontext
));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('instilledvideo', $moduleinstance);
$event->trigger();

$PAGE->set_url('/mod/instilledvideo/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

$output = $PAGE->get_renderer('mod_instilledvideo');

echo $output->header();

$heading = $moduleinstance->name;
echo $output->heading($heading);

echo $output->display_model($moduleinstance);

echo $output->footer();