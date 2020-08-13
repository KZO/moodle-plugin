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
 * Instilled Video module core interaction API
 *
 * @package mod
 * @subpackage instilledvideo
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function instilledvideo_supports($feature) {
  switch ($feature) {
    case FEATURE_MOD_INTRO:
      return true;
    case FEATURE_GRADE_HAS_GRADE:
      return true;
    default:
      return null;
  }
}

/**
 * Saves a new instance of the mod_instilledvideo into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_instilledvideo_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function instilledvideo_add_instance($moduleinstance, $mform = null) {
  global $DB;
  global $COURSE;

  $cmid = $moduleinstance->coursemodule;
  $draftitemid = $moduleinstance->videofile;

  $moduleinstance->timecreated = time();

  $id = $DB->insert_record('instilledvideo', $moduleinstance);

  $moduleinstance->instance = $id;
  $moduleinstance->id = $id;

  $context = context_module::instance($cmid);
  if ($draftitemid) {
    file_save_draft_area_files($draftitemid, $context->id, 'mod_instilledvideo', 'content', 0, array('subdirs' => 0, 'maxbytes' => $COURSE->maxbytes, 'maxfiles' => 1, 'accepted_types' => array('video')));
  }

  $task = new \mod_instilledvideo\task\send_video_to_instilled();
  $task->set_component('mod_instilledvideo');
  $task->set_next_run_time(time());
  $task->set_custom_data($moduleinstance);
  \core\task\manager::reschedule_or_queue_adhoc_task($task);

  instilledvideo_grade_item_update($moduleinstance);

  return $id;
}

/**
* Updates an instance of the mod_instilledvideo in the database.
*
* Given an object containing all the necessary data (defined in mod_form.php),
* this function will update an existing instance with new data.
*
* @param object $moduleinstance An object from the form in mod_form.php.
* @param mod_instilledvideo_mod_form $mform The form.
* @return bool True if successful, false otherwise.
*/
function instilledvideo_update_instance($moduleinstance, $mform = null) {
  global $DB;

  $moduleinstance->timemodified = time();
  $moduleinstance->id = $moduleinstance->instance;

  instilledvideo_grade_item_update($moduleinstance);

  return $DB->update_record('instilledvideo', $moduleinstance);
}

/**
* Removes an instance of the mod_instilledvideo from the database.
*
* @param int $id Id of the module instance.
* @return bool True if successful, false on failure.
*/
function instilledvideo_delete_instance($id) {
  global $DB;

  $exists = $DB->get_record('instilledvideo', array('id' => $id));
  if (!$exists) {
      return false;
  }

  $DB->delete_records('instilledvideo', array('id' => $id));

  return true;
}

/**
* Returns the lists of all browsable file areas within the given module context.
*
* The file area 'intro' for the activity introduction field is added automatically
* by {@link file_browser::get_file_info_context_module()}.
*
* @package     mod_instilledvideo
* @category    files
*
* @param stdClass $course.
* @param stdClass $cm.
* @param stdClass $context.
* @return string[].
*/
function instilledvideo_get_file_areas($course, $cm, $context) {
  return array();
}

/**
* File browsing support for mod_instilledvideo file areas.
*
* @package     mod_instilledvideo
* @category    files
*
* @param file_browser $browser.
* @param array $areas.
* @param stdClass $course.
* @param stdClass $cm.
* @param stdClass $context.
* @param string $filearea.
* @param int $itemid.
* @param string $filepath.
* @param string $filename.
* @return file_info Instance or null if not found.
*/
function instilledvideo_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
  return null;
}

/**
* Serves the files from the mod_instilledvideo file areas.
*
* @package     mod_instilledvideo
* @category    files
*
* @param stdClass $course The course object.
* @param stdClass $cm The course module object.
* @param stdClass $context The mod_instilledvideo's context.
* @param string $filearea The name of the file area.
* @param array $args Extra arguments (itemid, path).
* @param bool $forcedownload Whether or not force download.
* @param array $options Additional options affecting the file serving.
*/
function instilledvideo_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, $options = array()) {
  global $DB, $CFG;

  if ($context->contextlevel != CONTEXT_MODULE) {
      send_file_not_found();
  }

  require_login($course, true, $cm);
  send_file_not_found();
}

/**
 * Update/create grade item for given Instilled Video
 *
 * @category grade
 * @param object $instilledvideo object with extra cmidnumber
 * @param mixed $grades optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return object grade_item
 */
function instilledvideo_grade_item_update($instilledvideo, $grades=null) {
  global $CFG, $DB;
  require_once($CFG->dirroot.'/mod/instilledvideo/locallib.php');
  if (!function_exists('grade_update')) { // Workaround for buggy PHP versions.
    require_once($CFG->libdir.'/gradelib.php');
  }

  $params = array('itemname' => $instilledvideo->name);
  if (isset($instilledvideo->cmidnumber)) {
    $params['idnumber'] = $instilledvideo->cmidnumber;
  }

  $params['gradetype'] = GRADE_TYPE_VALUE;
  $params['grademax']  = 100;
  $params['grademin']  = 0;

  if ($grades === 'reset') {
    $params['reset'] = true;
    $grades = null;
  }

  return grade_update('mod/instilledvideo', $instilledvideo->course, 'mod', 'instilledvideo', $instilledvideo->id, 0, $grades, $params);
}

/**
 * Update grades in central gradebook
 *
 * @category grade
 * @param object $instilledvideo
 * @param int $userid specific user only, 0 mean all
 * @param bool $nullifnone
 */
function instilledvideo_update_grades($instilledvideo, $userid=0, $nullifnone=true) {
  global $CFG;
  require_once($CFG->libdir.'/gradelib.php');

  if ($grades = instilledvideo_get_user_grades($instilledvideo, $userid)) {
    instilledvideo_grade_item_update($instilledvideo, $grades);
  } else if ($userid and $nullifnone) {
    $grade = new stdClass();
    $grade->userid   = $userid;
    $grade->rawgrade = null;
    instilledvideo_grade_item_update($instilledvideo, $grade);
  } else {
    instilledvideo_grade_item_update($instilledvideo);
  }
}
