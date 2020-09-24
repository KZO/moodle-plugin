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
    $moduleinstance->timecreated = time();

    $id = $DB->insert_record('instilledvideo', $moduleinstance);

    $moduleinstance->instance = $id;
    $moduleinstance->id = $id;

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
 * Update/create grade item for given Instilled Video
 *
 * @category grade
 * @param object $instilledvideo object with extra cmidnumber
 * @param mixed $grades optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return object grade_item
 */
function instilledvideo_grade_item_update($instilledvideo, $grades=null) {
    global $CFG, $DB;

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

    if ($userid and $nullifnone) {
        $grade = new stdClass();
        $grade->userid   = $userid;
        $grade->rawgrade = null;
        instilledvideo_grade_item_update($instilledvideo, $grade);
    } else {
        instilledvideo_grade_item_update($instilledvideo);
    }
}
