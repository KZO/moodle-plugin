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
 * Instilled Student Gallery module core interaction API
 *
 * @package mod
 * @subpackage instilledgallery
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function instilledgallery_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the mod_instilledgallery into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_instilledgallery_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function instilledgallery_add_instance($moduleinstance, $mform = null) {
    global $DB;
    global $COURSE;

    $containerid = instilledgallery_create_container($moduleinstance->name);
    $groupid = instilledgallery_get_student_group();

    // Give students permission to upload.
    $roleuploadid = instilledgallery_get_student_role('CONTENT_CREATOR_UPLOAD_MEDIA');
    instilledgallery_set_container_permissions($containerid, $groupid, $roleuploadid);

    // Give students permission to record.
    $rolerecordid = instilledgallery_get_student_role('CONTENT_CREATOR_RECORD_MEDIA');
    instilledgallery_set_container_permissions($containerid, $groupid, $rolerecordid);

    $cmid = $moduleinstance->coursemodule;
    $moduleinstance->timecreated = time();
    $moduleinstance->containerid = $containerid;

    $id = $DB->insert_record('instilledgallery', $moduleinstance);

    $moduleinstance->instance = $id;
    $moduleinstance->id = $id;

    // instilledgallery_grade_item_update($moduleinstance);

    return $id;
}

/**
 * Updates an instance of the mod_instilledgallery in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_instilledgallery_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function instilledgallery_update_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    if (empty($moduleinstance->showcomments)) {
        $moduleinstance->showcomments = 0;
    }

    // instilledgallery_grade_item_update($moduleinstance);

    return $DB->update_record('instilledgallery', $moduleinstance);
}

/**
 * Removes an instance of the mod_instilledgallery from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function instilledgallery_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('instilledgallery', array('id' => $id));
    if (!$exists) {
        return false;
    }

    $DB->delete_records('instilledgallery', array('id' => $id));

    return true;
}

/**
 * Update/create grade item for given Instilled Student Gallery
 *
 * @category grade
 * @param object $instilledgallery object with extra cmidnumber
 * @param mixed $grades optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return object grade_item
 */
function instilledgallery_grade_item_update($instilledgallery, $grades=null) {
    global $CFG, $DB;

    if (!function_exists('grade_update')) { // Workaround for buggy PHP versions.
        require_once($CFG->libdir.'/gradelib.php');
    }

    $params = array('itemname' => $instilledgallery->name);

    if (isset($instilledgallery->cmidnumber)) {
        $params['idnumber'] = $instilledgallery->cmidnumber;
    }

    if (isset($instilledgallery->coursemodule)) {
        $params['idnumber'] = $instilledgallery->coursemodule;
    }

    if (isset($instilledgallery->grade)) {
        if ($instilledgallery->grade === 0) {
            $params['gradetype'] = GRADE_TYPE_NONE;
    
        } else if ($instilledgallery->grade > 0) {
            $params['gradetype'] = GRADE_TYPE_VALUE;
            $params['grademax']  = $instilledgallery->grade;
            $params['grademin']  = 0;
    
        } else if ($instilledgallery->grade < 0) {
            $params['gradetype'] = GRADE_TYPE_SCALE;
            $params['scaleid']   = -$instilledgallery->grade;
        }
    }
 
    if ($grades === 'reset') {
        $params['reset'] = true;
        $grades = null;
    }

    return grade_update('mod/instilledgallery', $instilledgallery->course, 'mod', 'instilledgallery', $instilledgallery->id, 0, $grades, $params);
}

/**
 * Update grades in central gradebook
 *
 * @category grade
 * @param object $instilledgallery
 * @param int $userid specific user only, 0 mean all
 * @param bool $nullifnone
 */
function instilledgallery_update_grades($instilledgallery, $userid=0, $nullifnone=true) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    if ($userid and $nullifnone) {
        $grade = new stdClass();
        $grade->userid   = $userid;
        $grade->rawgrade = null;
        instilledgallery_grade_item_update($instilledgallery, $grade);
    } else {
        instilledgallery_grade_item_update($instilledgallery);
    }
}

/**
* Create a container to hold the student video gallery
*/
function instilledgallery_create_container($title) {
    $method = 'POST';

    $tenanturl = get_config('local_instilled_media_gallery', 'tenanturl');

    $url = $tenanturl . '/api/containers/root/container';

    // Create a container with an arbitrary, unique title
    $postdata = '{"containers": {"title": "'.$title.'"}}';

    $result = \local_instilled_media_gallery\instilled::call_api($method, $url, $postdata);
    $result = json_decode($result);

    $containerid = $result->containers->id;

    return $containerid;
}

/**
* Get student group ID
*/
function instilledgallery_get_student_group() {
    $method = 'GET';

    $tenanturl = get_config('local_instilled_media_gallery', 'tenanturl');

    $url = $tenanturl . '/api/groups?name=regular';

    $result = \local_instilled_media_gallery\instilled::call_api($method, $url);
    $result = json_decode($result);

    $groupid = $result->groups[0]->id;
    
    return $groupid;
}

/**
* Get student role ID
*/
function instilledgallery_get_student_role($rolename) {
    $method = 'GET';

    $tenanturl = get_config('local_instilled_media_gallery', 'tenanturl');

    $url = $tenanturl . '/api/roles?name='.$rolename;

    $result = \local_instilled_media_gallery\instilled::call_api($method, $url);
    $result = json_decode($result);

    return $result->roles[0]->id;
}

/**
* Set permissions for a student video gallery container
*/
function instilledgallery_set_container_permissions($containerid, $groupid, $roleid) {
    $method = 'POST';

    $tenanturl = get_config('local_instilled_media_gallery', 'tenanturl');

    $url = $tenanturl . '/api/permissions';

    // Set permissions for the container
    $postdata = '{"permissions": {"role_id": "'.$roleid.'", "group_id": "'.$groupid.'", "container_id": "'.$containerid.'"}}';

    $result = \local_instilled_media_gallery\instilled::call_api($method, $url, $postdata);
    $result = json_decode($result);
}