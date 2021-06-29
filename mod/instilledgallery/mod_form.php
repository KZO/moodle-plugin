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
 * Form for mod_instilledgallery
 *
 * @package   mod_instilledgallery
 * @copyright 2020 Instilled <support@instilled.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . '/local/instilled_media_gallery/locallib.php');

class mod_instilledgallery_mod_form extends moodleform_mod {

    public function definition() {
        global $CFG, $USER, $DB, $PAGE;

        $courseid = optional_param('course', null, PARAM_INT);
        $updateid = optional_param('update', null, PARAM_INT);

        if (isset($courseid)) {
            $context = context_course::instance($courseid);
        } else if (isset($updateid)) {
            $cm = $DB->get_record('course_modules', array('id' => $updateid));
            $courseid = $cm->course;
            $context = context_course::instance($courseid);
            $activitymodule = $DB->get_record('instilledgallery', array('id' => $cm->instance));
            $mediumid = $activitymodule->mediumid;
        }

        $instilled = new \local_instilled_media_gallery\instilled();
        $instilledaccesskey = $instilled->authenticate_user($context);

        $mform =& $this->_form;

        // General options.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('galleryname', 'instilledgallery'), array('size' => '64'));

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        // Adding the standard "intro" and "introformat" fields.
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }

        $tenanturl = get_config('local_instilled_media_gallery', 'tenanturl');
        $defaultcontainer = get_config('local_instilled_media_gallery', 'defaultcontainer');

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }
}
